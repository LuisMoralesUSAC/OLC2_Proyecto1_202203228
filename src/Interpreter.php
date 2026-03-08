<?php 

use Context\ProgramContext;
use Context\PrintStatementContext;
use Context\VarDeclarationContext;
use Context\AssignmentStatementContext;
use Context\IfStatementContext;
use Context\WhileStatementContext;
use Context\ContinueStatementContext;
use Context\BreakStatementContext;
use Context\ReturnStatementContext;
use Context\FunctionDeclarationContext;
use Context\FunctionCallStatementContext;
use Context\ArrayAssignmentStatementContext;
use Context\BlockStatementContext;
use Context\EqualityExpressionContext;
use Context\AddExpressionContext;
use Context\ProductExpressionContext;
use Context\PrimaryExpressionContext;
use Context\GroupedExpressionContext;
use Context\IntExpressionContext;
use Context\ReferenceExpressionContext;
use Context\BoolExpressionContext;
use Context\FunctionCallExpressionContext;
use Context\ArrayExpressionContext;
use Context\ArrayAccessExpressionContext;
use Context\ParameterListContext;
use Context\ArgumentListContext;
use Context\VarDeclarationTypedContext;
use Context\VarDeclarationTypedEmptyContext;
use Context\ShortVarDeclarationContext;
use Context\ConstDeclarationContext;
use Context\FloatExpressionContext;
use Context\StringExpressionContext;
use Context\RuneExpressionContext;
use Context\OrExpressionContext;
use Context\AndExpressionContext;
use Context\RelationalExpressionContext;
use Context\NegativeExpressionContext;
use Context\NotExpressionContext;
use Context\PrintExpressionContext;
use Context\SwitchStatementContext;
use Context\CaseClauseContext;
use Context\DefaultClauseContext;
use Context\ForStatementContext;
use Context\ForConditionStatementContext;
use Context\ForInfiniteStatementContext;
use Context\ForInitVarTypedContext;
use Context\ForInitShortContext;
use Context\ForInitAssignContext;
use Context\ForPostAssignContext;
use Context\ForPostIncrementContext;
use Context\ForPostDecrementContext;
use Context\IncrementStatementContext;
use Context\DecrementStatementContext;

class Interpreter extends GrammarBaseVisitor {
    public $console;
    public $env;

    public $embebed;

    public function __construct() {
        $this->console = "";
        $this->env = new Environment();
        $this->embebed = include __DIR__ . "/Natives.php";
        foreach ($this->embebed as $name => $function) {
            $this->env->set($name, $function);
        }
    }

    public function visitProgram(ProgramContext $ctx) {                  
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        return $this->console;
    }

    public function visitPrintStatement(PrintStatementContext $ctx) {
        $value = $this->visit($ctx->e());   
        if (is_bool($value)) {
            $value = $value ? "true" : "false";
        }
        $this->console .= $value . "\n";
        return $value;
    }

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->set($varName, $value);
        return $value;
    }

    public function visitVarDeclarationTyped(VarDeclarationTypedContext $ctx) {
        $varName = $ctx->ID()->getText();
        $typeName = $ctx->type()->getText();
        $value = $this->visit($ctx->e());
    
        $this->env->set($varName, $value);
        return $value;
    }

    public function visitVarDeclarationTypedEmpty(VarDeclarationTypedEmptyContext $ctx) {
        $varName = $ctx->ID()->getText();
        $typeName = $ctx->type()->getText();
    
        $defaultValue = $this->getDefaultValue($typeName);
        $this->env->set($varName, $defaultValue);
        return $defaultValue;
    }

    public function visitShortVarDeclaration(ShortVarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());

        $this->env->set($varName, $value);
        return $value;
    }

    public function visitConstDeclaration(ConstDeclarationContext $ctx) {
        $constName = $ctx->ID()->getText();
        $typeName = $ctx->type()->getText();
        $value = $this->visit($ctx->e());
    
        $this->env->set($constName, $value);
        return $value;
    }
    private function getDefaultValue($typeName) {
        switch($typeName) {
            case 'int32':
                return 0;
            case 'float32':
                return 0.0;
            case 'bool':
                return false;
            case 'rune':
                return '\u0000';
            case 'string':
                return "";
            default:
                return null;
        }
    }

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->assign($varName, $value);
        return $value;
    }

    public function visitIfStatement(IfStatementContext $ctx) {
        $condition = $this->visit($ctx->e());        
        if ($condition) {            
            $flow = $this->visit($ctx->block());
            if ($flow instanceof FlowType) {
                return $flow;
            }
        } else if ($ctx->else() !== null) {
            $flow = $this->visit($ctx->else());
            if ($flow instanceof FlowType) {
                return $flow;
            }
        }
    }

    public function visitForStatement(ForStatementContext $ctx) {
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
                
        if ($ctx->forInit() !== null) {
            $this->visit($ctx->forInit());
        }
                
        while (true) {
            $condition = true;
            if ($ctx->forCond() !== null) {
                $condition = $this->visit($ctx->forCond()->e());
            }
                
            if (!$condition) {
                break;
            }
                
            $flow = $this->visit($ctx->block());
        
            if ($flow instanceof BreakType) {
                break;
            }
                
            if ($flow instanceof ReturnType) {
                $this->env = $prevEnv;
                return $flow;
            }
                
            if ($ctx->forPost() !== null) {
                $this->visit($ctx->forPost());
            }
        }
                
        $this->env = $prevEnv;
        return null;
    }

    public function visitForConditionStatement(ForConditionStatementContext $ctx) {
        while (true) {
            $condition = $this->visit($ctx->e());
                
            if (!$condition) {
                break;
            }
                
            $flow = $this->visit($ctx->block());
                
            if ($flow instanceof BreakType) {
                break;
            }
                
            if ($flow instanceof ReturnType) {
                return $flow;
            }
        }
                
        return null;
    }

    public function visitForInfiniteStatement(ForInfiniteStatementContext $ctx) {
        while (true) {
            $flow = $this->visit($ctx->block());
                
            if ($flow instanceof BreakType) {
                break;
            }
                
            if ($flow instanceof ReturnType) {
                return $flow;
            }
        }
                
        return null;
    }

    public function visitForInitVarTyped(ForInitVarTypedContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->set($varName, $value);
        return $value;
    }

    public function visitForInitShort(ForInitShortContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->set($varName, $value);
        return $value;
    }

    public function visitForInitAssign(ForInitAssignContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->assign($varName, $value);
        return $value;
    }

    public function visitForPostAssign(ForPostAssignContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->assign($varName, $value);
        return $value;
    }

    public function visitForPostIncrement(ForPostIncrementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->env->get($varName);
        $this->env->assign($varName, $value + 1);
        return null;
    }

    public function visitForPostDecrement(ForPostDecrementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->env->get($varName);
        $this->env->assign($varName, $value - 1);
        return null;
    }

    public function visitIncrementStatement(IncrementStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->env->get($varName);
        $this->env->assign($varName, $value + 1);
        return null;
    }

    public function visitDecrementStatement(DecrementStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->env->get($varName);
        $this->env->assign($varName, $value - 1);
        return null;
    }

    public function visitContinueStatement(ContinueStatementContext $ctx) {        
        return new ContinueType();
    }

    public function visitBreakStatement(BreakStatementContext $ctx) {
        return new BreakType();
    }

    public function visitReturnStatement(ReturnStatementContext $ctx) {
        $value = null;
        if ($ctx->e() !== null) {
            $value = $this->visit($ctx->e());
        }
        return new ReturnType($value);
    }

    public function visitFunctionDeclaration(FunctionDeclarationContext $ctx) {
        $params = array();
        if ($ctx->params() !== null) {
            $params = $this->visit($ctx->params());
        }
        $function = new Foreign($ctx, $this->env, $params);
        $this->env->set($ctx->ID()->getText(), $function);
    }

    public function visitFunctionCallStatement(FunctionCallStatementContext $ctx) {
        $function = $this->env->get($ctx->ID()->getText());
        $args = array();
        if ($ctx->args() !== null) {
            $args = $this->visit($ctx->args());            
        }
        if (!($function instanceof Invocable)) {
            throw new Exception("La variable " . $ctx->ID()->getText() . " no es una función invocable");
        }
        if ($function->get_arity() !== count($args)) {
            throw new Exception("La función " . $ctx->ID()->getText() . " espera " . $function->get_arity() . " argumentos, pero se le dieron " . count($args));
        }
        return $function->invoke($this, $args);
    }

    public function visitArrayAssignmentStatement(ArrayAssignmentStatementContext $ctx) {
        $arrayName = $ctx->ID()->getText();
        // Obtener la referencia al arreglo desde el entorno
        $array = &$this->env->get_ref($arrayName);
        if (!is_array($array)) {
            throw new Exception("La variable " . $arrayName . " no es un arreglo");
        }
        // Evaluar los índices y almacenarlos en un arreglo
        $indices = array();
        foreach ($ctx->index as $index) {
            $idx = $this->visit($index);
            if (!is_int($idx)) {
                throw new Exception("El índice debe ser un entero, se recibió: " . gettype($idx));
            }
            $indices[] = $idx;
        }
        $value = $this->visit($ctx->assign); 
        // Navegar hasta el arreglo interno correcto
        $current = &$array;
        for ($i = 0; $i < count($indices) - 1; $i++) {
            $idx = $indices[$i];
            if (!array_key_exists($idx, $current)) {
                throw new Exception("Índice fuera de rango: " . $idx);
            }
            if (!is_array($current[$idx])) {
                throw new Exception("El elemento en el índice " . $idx . " no es un arreglo");
            }
            $current = &$current[$idx];
        }
        // Asignar el valor al índice final
        $finalIdx = end($indices);
        if (!array_key_exists($finalIdx, $current)) {
            throw new Exception("Índice fuera de rango: " . $finalIdx);
        }
        $current[$finalIdx] = $value;        
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);                
        foreach ($ctx->stmt() as $stmt) {            
            $flow = $this->visit($stmt);            
            if ($flow instanceof FlowType) {
                $this->env = $prevEnv;                
                return $flow;
            }
        }
        $this->env = $prevEnv;        
    }

    public function visitOrExpression(OrExpressionContext $ctx) {
        if ($ctx->logicalOr() !== null) {
            $left = $this->visit($ctx->logicalOr());
        
            if ($left === true) {
                return true;
            }
                
            $right = $this->visit($ctx->logicalAnd());
            return $left || $right;
        } else {
            return $this->visit($ctx->logicalAnd());
        }
    }

    public function visitAndExpression(AndExpressionContext $ctx) {
        if ($ctx->logicalAnd() !== null) {
            $left = $this->visit($ctx->logicalAnd());

            if ($left === false) {
                return false;
            }
                
            $right = $this->visit($ctx->eq());
            return $left && $right;
        } else {
            return $this->visit($ctx->eq());
        }
    }

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        if ($ctx->right !== null) {
            $left = $this->visit($ctx->left);
            $right = $this->visit($ctx->right);
            $op = $ctx->op->getText();
                
            switch ($op) {
                case '==':
                    return $left == $right;
                case '!=':
                    return $left != $right;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->left);
        }
    }

    public function visitRelationalExpression(RelationalExpressionContext $ctx) {
        if ($ctx->right !== null) {
            $left = $this->visit($ctx->left);
            $right = $this->visit($ctx->right);
            $op = $ctx->op->getText();

            switch ($op) {
                case '>':
                    return $left > $right;
                case '>=':
                    return $left >= $right;
                case '<':
                    return $left < $right;
                case '<=':
                    return $left <= $right;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->left);
        }
    }

    public function visitAddExpression(AddExpressionContext $ctx) {
        if ($ctx->add() !== null) {
            $add = $this->visit($ctx->add());
            $prod = $this->visit($ctx->prod());
            $op = $ctx->op->getText();

            switch ($op) {
                case '+':
                    return $add + $prod;
                case '-':
                    return $add - $prod;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->prod());
        }
    }

    public function visitProductExpression(ProductExpressionContext $ctx) {
        if ($ctx->prod() !== null) {
            $prod = $this->visit($ctx->prod());
            $unary = $this->visit($ctx->unary());
            $op = $ctx->op->getText();

            switch ($op) {
                case '*':
                    return $prod * $unary;
                case '/':
                    if ($unary == 0) {
                        throw new Exception("División por cero");
                    }
                    return $prod / $unary;
                case '%':
                    if ($unary == 0) {
                        throw new Exception("Módulo por cero");
                    }
                    return $prod % $unary;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->unary());
        }
    }

    public function visitNegativeExpression(NegativeExpressionContext $ctx) {        
        return - $this->visit($ctx->unary());
    }

    public function visitNotExpression(NotExpressionContext $ctx) {        
        return ! $this->visit($ctx->unary());
    }

    public function visitPrimaryExpression(PrimaryExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->e());
    }

    public function visitIntExpression(IntExpressionContext $ctx) {
        return intval($ctx->INT()->getText());
    }  
    
    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        return $this->env->get($varName);
    }

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        return $ctx->bool->getText() === 'true';
    }

    public function visitFunctionCallExpression(FunctionCallExpressionContext $ctx) {
        $function = $this->env->get($ctx->ID()->getText());
        $args = array();
        if ($ctx->args() !== null) {
            $args = $this->visit($ctx->args());            
        }
        if (!($function instanceof Invocable)) {
            throw new Exception("La expresión no es una función invocable");
        }
        if ($function->get_arity() !== count($args)) {
            throw new Exception("La función espera " . $function->get_arity() . " argumentos, pero se le dieron " . count($args));
        }
        return $function->invoke($this, $args);
    }

    public function visitArrayExpression(ArrayExpressionContext $ctx) {
        $elements = array();
        foreach ($ctx->e() as $element) {
            $elements[] = $this->visit($element);
        }
        return $elements;
    }

    public function visitArrayAccessExpression(ArrayAccessExpressionContext $ctx) {
        $array = $this->env->get($ctx->ID()->getText());        
        foreach ($ctx->e() as $index) {
            $idx = $this->visit($index);
            if (!is_array($array)) {
                throw new Exception("La variable " . $ctx->ID()->getText() . " no es un arreglo");
            }
            if (!array_key_exists($idx, $array)) {
                throw new Exception("Índice fuera de rango: " . $idx);
            }
            $array = $array[$idx];
        }
        return $array;
    }

    public function visitParameterList(ParameterListContext $ctx) {
        $params = array();
        foreach ($ctx->ID() as $id) {
            $params[] = $id->getText();
        }
        return $params;
    }

    public function visitArgumentList(ArgumentListContext $ctx) {
        $args = array();
        foreach ($ctx->e() as $arg) {
            $args[] = $this->visit($arg);
        }
        return $args;
    }

    public function visitFloatExpression(FloatExpressionContext $ctx) {
        return floatval($ctx->FLOAT()->getText());
    }

    public function visitStringExpression(StringExpressionContext $ctx) {
        $text = $ctx->STRING()->getText();
        return substr($text, 1, -1);
    }

    public function visitRuneExpression(RuneExpressionContext $ctx) {
        $text = $ctx->RUNE()->getText();
        return substr($text, 1, -1);
    }

    public function visitPrintExpression(PrintExpressionContext $ctx) {
        $value = $this->visit($ctx->e());   
        if (is_bool($value)) {
            $value = $value ? "true" : "false";
        }
        $this->console .= $value . "\n";
        return $value;
    }

    public function visitSwitchStatement(SwitchStatementContext $ctx) {
        $switchValue = $this->visit($ctx->e());
    
        $matched = false;
    
        foreach ($ctx->switchCase() as $caseCtx) {
            if ($caseCtx instanceof CaseClauseContext) {

                foreach ($caseCtx->e() as $caseExpr) {
                    $caseValue = $this->visit($caseExpr);

                    if ($switchValue == $caseValue) {
                        $matched = true;
                    
                        foreach ($caseCtx->stmt() as $stmt) {
                            $flow = $this->visit($stmt);
                        
                            if ($flow instanceof BreakType) {
                                return null;
                            }
                            if ($flow instanceof ReturnType) {
                                return $flow;
                            }
                        }
                    
                        return null;
                    }
                }
            }
        }

        if (!$matched) {
            foreach ($ctx->switchCase() as $caseCtx) {
                if ($caseCtx instanceof DefaultClauseContext) {
                    foreach ($caseCtx->stmt() as $stmt) {
                        $flow = $this->visit($stmt);

                        if ($flow instanceof BreakType) {
                            return null;
                        }
                        if ($flow instanceof ReturnType) {
                            return $flow;
                        }
                    }
                    break;
                }
            }
        }
                    
        return null;
    }

    public function visitCaseClause(CaseClauseContext $ctx) {
        throw new Exception("visitCaseClause no debería llamarse directamente");
    }

    public function visitDefaultClause(DefaultClauseContext $ctx) {
        throw new Exception("visitDefaultClause no debería llamarse directamente");
    }
}