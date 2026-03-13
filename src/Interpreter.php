<?php 

use Context\ProgramContext;
use Context\PrintStatementContext;
use Context\VarDeclarationContext;
use Context\AssignmentStatementContext;
use Context\IfStatementContext;
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
use Context\NilExpressionContext;
use Context\ModuleFunctionCallContext;
use Context\ModuleFunctionExpressionContext;
use Context\IdExpressionContext;
use Context\ArrayLiteralExpressionContext;
use Context\SimpleArrayElementContext;
use Context\NestedArrayElementContext;
use Context\ArrayElementsContext;
use Context\VarDeclarationTypedMultipleContext;
use Context\ShortVarDeclarationMultipleContext;
use Context\TrueExpressionContext;
use Context\FalseExpressionContext;
use Context\CompoundAssignmentStatementContext;
use Context\SingleReturnTypeContext;
use Context\MultipleReturnTypesContext;
use Context\SingleReturnValueContext;
use Context\MultipleReturnValuesContext;
use Context\AddressOfExpressionContext;
use Context\PointerTypeContext;

class Interpreter extends GrammarBaseVisitor {
    public $console;
    public $env;
    public $embebed;
    public $tablaSimbolos;
    public $ambitoActual;
    public $reporteErrores;

    public function __construct() {
        $this->console = "";
        $this->env = new Environment();
        $this->tablaSimbolos = new TablaSimbolos();
        $this->ambitoActual = "global";
        $this->reporteErrores = new ReporteErrores();
        $this->embebed = include __DIR__ . "/Natives.php";
        foreach ($this->embebed as $name => $function) {
            $this->env->set($name, $function);
        }
    }

    public function visitProgram(ProgramContext $ctx) {
        $mainFunction = null;
        $mainCount = 0;
        
        foreach ($ctx->stmt() as $stmt) {
            if ($stmt instanceof FunctionDeclarationContext) {
                $this->visit($stmt);
                
                $functionName = $stmt->ID()->getText();
                if ($functionName === "main") {
                    $mainCount++;

                    if ($stmt->params() !== null) {
                        $this->registrarErrorSemantico(
                            "La función 'main' no debe tener parámetros",
                            $stmt
                        );
                    }
                    
                    if ($stmt->returnTypes() !== null) {
                        $this->registrarErrorSemantico(
                            "La función 'main' no debe tener tipo de retorno",
                            $stmt
                        );
                    }
                    try {
                        $mainFunction = $this->env->get("main");
                    } catch (Exception $e) {
                    }
                }
            }
        }
        
        if ($mainCount === 0) {
            $errorMsg = "Error: No se encontró la función 'main'. Todo programa debe tener una función main.";
            $this->console .= $errorMsg . "\n";
            return $this->console;
        }
        
        if ($mainCount > 1) {
            $errorMsg = "Error: Se encontraron " . $mainCount . " funciones 'main'. Solo debe existir una.";
            $this->console .= $errorMsg . "\n";
            return $this->console;
        }
        foreach ($ctx->stmt() as $stmt) {
            if (!($stmt instanceof FunctionDeclarationContext)) {
                $linea = $stmt->getStart()->getLine();
                $this->registrarErrorSemantico(
                    "El código ejecutable debe estar dentro de la función 'main' o funciones auxiliares",
                    $stmt
                );
            }
        }
        if ($mainFunction !== null && $mainFunction instanceof Invocable) {
            $mainFunction->invoke($this, []);
        }
        
        return $this->console;
    }

    public function visitPrintStatement(PrintStatementContext $ctx) {
        $value = $this->visit($ctx->e());
        if ($value === null) {
            $this->console .= "nil\n";
            return $value;
        }
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
        $tipoInferido = Type::inferType($value);
        $this->registrarSimbolo($varName, $tipoInferido, $value, $ctx);
        return $value;
    }

    public function visitVarDeclarationTyped(VarDeclarationTypedContext $ctx) {
        $varName = $ctx->ID()->getText();
        $typeCtx = $ctx->type();
        
        if ($typeCtx->INT() !== null) {
            $arrayInfo = Type::parseArrayType($typeCtx);
            
            if ($ctx->e() !== null) {
                $value = $this->visit($ctx->e());
                
                if (!Type::validateArrayStructure($value, $arrayInfo['dimensions'], $arrayInfo['baseType'])) {
                    throw new Exception(
                        "El valor no coincide con el tipo de arreglo declarado"
                    );
                }
            } else {
                $value = Type::createArrayWithDefaults(
                    $arrayInfo['dimensions'],
                    $arrayInfo['baseType']
                );
            }
            
            $this->env->set($varName, $value);
            
            $tipoStr = Type::arrayTypeToString($arrayInfo['dimensions'], $arrayInfo['baseType']);
            $this->registrarSimbolo($varName, $tipoStr, $value, $ctx);
            
            return $value;
        }
        
        $typeName = $typeCtx->getText();
        $value = $this->visit($ctx->e());

        if (!Type::isCompatible($value, $typeName)) {
            $inferredType = Type::inferType($value);
            $mensaje = "No se puede asignar un valor de tipo '" . $inferredType . 
                      "' a la variable '" . $varName . "' de tipo '" . $typeName . "'";
            $this->registrarErrorSemantico($mensaje, $ctx);
            $value = Type::getDefault($typeName);
        } else {
            if ($value !== null) {
                $value = Type::cast($value, $typeName);
            }
        }

        $this->env->set($varName, $value);
        $this->registrarSimbolo($varName, $typeName, $value, $ctx);
        
        return $value;
    }
    
    public function visitArrayLiteralExpression(ArrayLiteralExpressionContext $ctx) {
        $size = intval($ctx->arrayLiteral()->INT()->getText());
        $typeCtx = $ctx->arrayLiteral()->type();
        
        if ($typeCtx->INT() !== null) {
            $arrayInfo = Type::parseArrayType($typeCtx);
            $baseType = $arrayInfo['baseType'];
            $elementDimensions = $arrayInfo['dimensions'];
        } else {
            $baseType = $typeCtx->getText();
            $elementDimensions = [];
        }
        
        $elements = [];
        if ($ctx->arrayLiteral()->arrayElements() !== null) {
            $elements = $this->processArrayElements(
                $ctx->arrayLiteral()->arrayElements(),
                $elementDimensions,
                $baseType
            );
        }
        
        if (count($elements) !== $size) {
            throw new Exception(
                "El arreglo declara " . $size . " elementos pero se proporcionaron " . count($elements)
            );
        }
        
        return $elements;
    }
    
    public function visitArrayElements(ArrayElementsContext $ctx) {
        return [];
    }
    
    private function processArrayElements($ctx, $dimensions, $baseType) {
        $elements = [];
        
        foreach ($ctx->arrayElement() as $elementCtx) {
            if ($elementCtx instanceof SimpleArrayElementContext) {
                $value = $this->visit($elementCtx->e());
                
                if (empty($dimensions)) {
                    if (!Type::isCompatible($value, $baseType)) {
                        $inferredType = Type::inferType($value);
                        throw new Exception(
                            "Tipo de elemento incorrecto: se esperaba '" . $baseType . 
                            "' pero se recibió '" . $inferredType . "'"
                        );
                    }
                    if ($value !== null) {
                        $value = Type::cast($value, $baseType);
                    }
                }
                
                $elements[] = $value;
                
            } elseif ($elementCtx instanceof NestedArrayElementContext) {
                $nestedElements = $this->processArrayElements(
                    $elementCtx->arrayElements(),
                    array_slice($dimensions, 1),
                    $baseType
                );
                $elements[] = $nestedElements;
            }
        }
        
        return $elements;
    }

    public function visitVarDeclarationTypedEmpty(VarDeclarationTypedEmptyContext $ctx) {
        $varName = $ctx->ID()->getText();
        $typeCtx = $ctx->type();
        
        if ($typeCtx->INT() !== null) {
            $arrayInfo = Type::parseArrayType($typeCtx);
            $defaultValue = Type::createArrayWithDefaults(
                $arrayInfo['dimensions'],
                $arrayInfo['baseType']
            );
            
            $this->env->set($varName, $defaultValue);
            
            $tipoStr = Type::arrayTypeToString($arrayInfo['dimensions'], $arrayInfo['baseType']);
            $this->registrarSimbolo($varName, $tipoStr, $defaultValue, $ctx);
            
            return $defaultValue;
        }
        
        $typeName = $typeCtx->getText();
        $defaultValue = $this->getDefaultValue($typeName);
        $this->env->set($varName, $defaultValue);
        $this->registrarSimbolo($varName, $typeName, $defaultValue, $ctx);
        return $defaultValue;
    }

    public function visitShortVarDeclaration(ShortVarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->set($varName, $value);
        $tipoInferido = Type::inferType($value);
        $this->registrarSimbolo($varName, $tipoInferido, $value, $ctx);
        return $value;
    }

    public function visitConstDeclaration(ConstDeclarationContext $ctx) {
        $constName = $ctx->ID()->getText();
        $typeName = $ctx->type()->getText();
        $value = $this->visit($ctx->e());
        
        if (!Type::isCompatible($value, $typeName)) {
            $inferredType = Type::inferType($value);
            $mensaje = "No se puede asignar un valor de tipo '" . $inferredType . 
                      "' a la constante '" . $constName . "' de tipo '" . $typeName . "'";
            $this->registrarErrorSemantico($mensaje, $ctx);
            $value = Type::getDefault($typeName);
        } else {
            if ($value !== null) {
                $value = Type::cast($value, $typeName);
            }
        }
        
        $this->env->set($constName, $value);
        $this->registrarSimbolo($constName, $typeName, $value, $ctx);
        return $value;
    }
    private function getDefaultValue($typeName) {
        return Type::getDefault($typeName);
    }

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $newValue = $this->visit($ctx->e());
        try {
            $currentValue = $this->env->get($varName);
        } catch (Exception $e) {
            $this->registrarErrorSemantico("Variable '" . $varName . "' no definida", $ctx);
            return null;
        }
        if ($currentValue instanceof Reference) {
            $currentValue->setValue($newValue);
            return $newValue;
        }
        $existingType = Type::inferType($currentValue);
        $newType = Type::inferType($newValue);
        if ($existingType !== Type::NIL && !Type::isCompatible($newValue, $existingType)) {
            $mensaje = "No se puede asignar un valor de tipo '" . $newType . 
                      "' a la variable '" . $varName . "' de tipo '" . $existingType . "'";
            $this->registrarErrorSemantico($mensaje, $ctx);
            return $currentValue;
        }
        if ($existingType !== Type::NIL && $newValue !== null) {
            $newValue = Type::cast($newValue, $existingType);
        }
        $this->env->assign($varName, $newValue);
        return $newValue;
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
        $ambitoAnterior = $this->ambitoActual;
        $this->ambitoActual = "for";

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
                $this->ambitoActual = $ambitoAnterior;
                return $flow;
            }
            
            if ($ctx->forPost() !== null) {
                $this->visit($ctx->forPost());
            }
        }

        $this->env = $prevEnv;
        $this->ambitoActual = $ambitoAnterior;
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
        if ($ctx->returnValues() === null) {
            return new ReturnType(null);
        }
        
        $returnValuesCtx = $ctx->returnValues();
        
        if ($returnValuesCtx instanceof SingleReturnValueContext) {
            $value = $this->visit($returnValuesCtx->e());
            return new ReturnType($value);
        } elseif ($returnValuesCtx instanceof MultipleReturnValuesContext) {
            $values = [];
            foreach ($returnValuesCtx->exprListReturn()->e() as $expr) {
                $values[] = $this->visit($expr);
            }
            return new ReturnType($values);
        }
        
        return new ReturnType(null);
    }

    public function visitFunctionDeclaration(FunctionDeclarationContext $ctx) {
        $nombreFuncion = $ctx->ID()->getText();
        $params = [];
        $tiposParams = [];
        
        if ($ctx->params() !== null) {
            $paramsData = $this->visit($ctx->params());
            $params = $paramsData['nombres'];
            $tiposParams = $paramsData['tipos'];
        }
        
        $tiposRetorno = null;
        if ($ctx->returnTypes() !== null) {
            $returnTypesCtx = $ctx->returnTypes();
            if ($returnTypesCtx instanceof SingleReturnTypeContext) {
                $typeCtx = $returnTypesCtx->type();
                if ($typeCtx->INT() !== null) {
                    $tiposRetorno = Type::parseArrayType($typeCtx);
                } else {
                    $tiposRetorno = $typeCtx->getText();
                }
            } elseif ($returnTypesCtx instanceof MultipleReturnTypesContext) {
                $tiposRetorno = [];
                foreach ($returnTypesCtx->typeList()->type() as $typeCtx) {
                    if ($typeCtx->INT() !== null) {
                        $tiposRetorno[] = Type::parseArrayType($typeCtx);
                    } else {
                        $tiposRetorno[] = $typeCtx->getText();
                    }
                }
            }
        }
        $function = new Foreign($ctx, $this->env, $params, $tiposParams, $tiposRetorno);
        $this->env->set($nombreFuncion, $function);
        $this->registrarSimbolo($nombreFuncion, "function", $function, $ctx);
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
        $arrayOrRef = $this->env->get($arrayName);
        $isReference = ($arrayOrRef instanceof Reference);
        if ($isReference) {
            $array = $arrayOrRef->getValue();
        } else {
            $array = &$this->env->get_ref($arrayName);
        }
        if (!is_array($array)) {
            throw new Exception("La variable " . $arrayName . " no es un arreglo");
        }
        $indices = array();
        foreach ($ctx->index as $index) {
            $idx = $this->visit($index);
            if (!is_int($idx)) {
                throw new Exception("El índice debe ser un entero, se recibió: " . gettype($idx));
            }
            $indices[] = $idx;
        }
        $value = $this->visit($ctx->assign);
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
        $finalIdx = end($indices);
        if (!array_key_exists($finalIdx, $current)) {
            throw new Exception("Índice fuera de rango: " . $finalIdx);
        }
        $current[$finalIdx] = $value;
        if ($isReference) {
            $arrayOrRef->setValue($array);
        }
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
        $ambitoAnterior = $this->ambitoActual;
        $this->ambitoActual = "bloque";
        foreach ($ctx->stmt() as $stmt) {            
            $flow = $this->visit($stmt);            
            if ($flow instanceof FlowType) {
                $this->env = $prevEnv;
                $this->ambitoActual = $ambitoAnterior;
                return $flow;
            }
        }
        $this->env = $prevEnv;
        $this->ambitoActual = $ambitoAnterior;
    }

    public function visitOrExpression(OrExpressionContext $ctx) {
        if ($ctx->logicalOr() !== null) {
            $left = $this->visit($ctx->logicalOr());
            $leftType = Type::inferType($left);
            if (!Type::canUseLogical($leftType)) {
                $mensaje = "Operador '||' requiere operandos de tipo 'bool', se recibió '" . $leftType . "'";
                $this->registrarErrorSemantico($mensaje, $ctx);
                return false;
            }
            if ($left === true) {
                return true;
            }
            
            $right = $this->visit($ctx->logicalAnd());
            $rightType = Type::inferType($right);

            if (!Type::canUseLogical($rightType)) {
                $mensaje = "Operador '||' requiere operandos de tipo 'bool', se recibió '" . $rightType . "'";
                $this->registrarErrorSemantico($mensaje, $ctx);
                return false;
            }
            
            return $left || $right;
        } else {
            return $this->visit($ctx->logicalAnd());
        }
    }

    public function visitAndExpression(AndExpressionContext $ctx) {
        if ($ctx->logicalAnd() !== null) {
            $left = $this->visit($ctx->logicalAnd());
            $leftType = Type::inferType($left);
            if (!Type::canUseLogical($leftType)) {
                $mensaje = "Operador '&&' requiere operandos de tipo 'bool', se recibió '" . $leftType . "'";
                $this->registrarErrorSemantico($mensaje, $ctx);
                return false;
            }
            
            if ($left === false) {
                return false;
            }
            
            $right = $this->visit($ctx->eq());
            $rightType = Type::inferType($right);

            if (!Type::canUseLogical($rightType)) {
                $mensaje = "Operador '&&' requiere operandos de tipo 'bool', se recibió '" . $rightType . "'";
                $this->registrarErrorSemantico($mensaje, $ctx);
                return false;
            }
            
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
            $leftType = Type::inferType($left);
            $rightType = Type::inferType($right);
            
            if (!Type::canCompareEquality($leftType, $rightType)) {
                $mensaje = "No se puede comparar tipo '" . $leftType . 
                          "' con tipo '" . $rightType . "' usando operador '" . $op . "'";
                $this->registrarErrorSemantico($mensaje, $ctx);
                return false;
            }
            
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
            $leftType = Type::inferType($left);
            $rightType = Type::inferType($right);
            
            if (!Type::canCompareRelational($leftType, $rightType)) {
                $mensaje = "No se puede comparar tipo '" . $leftType . 
                          "' con tipo '" . $rightType . "' usando operador '" . $op . "'";
                $this->registrarErrorSemantico($mensaje, $ctx);
                return false;
            }

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
            
            if (Type::propagateNil($add, $prod, $op)) {
                return null;
            }
            
            $leftType = Type::inferType($add);
            $rightType = Type::inferType($prod);
            
            switch ($op) {
                case '+':
                    $resultType = Type::getAdditionResultType($leftType, $rightType);
                    if ($resultType === null) {
                        $mensaje = "Operación '+' inválida entre tipos '" . $leftType . "' y '" . $rightType . "'";
                        $this->registrarErrorSemantico($mensaje, $ctx);
                        return null;
                    }
                    if ($resultType === Type::STRING) {
                        return $add . $prod;
                    }
                    return $add + $prod;
                    
                case '-':
                    $resultType = Type::getArithmeticResultType($leftType, $rightType, '-');
                    if ($resultType === null) {
                        $mensaje = "Operación '-' inválida entre tipos '" . $leftType . "' y '" . $rightType . "'";
                        $this->registrarErrorSemantico($mensaje, $ctx);
                        return null;
                    }
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
            
            if (Type::propagateNil($prod, $unary, $op)) {
                return null;
            }
            
            $leftType = Type::inferType($prod);
            $rightType = Type::inferType($unary);

            switch ($op) {
                case '*':
                    if (($leftType === Type::STRING || $leftType === Type::RUNE) && $rightType === Type::INT32) {
                        return str_repeat($prod, $unary);
                    }
                    if ($leftType === Type::INT32 && ($rightType === Type::STRING || $rightType === Type::RUNE)) {
                        return str_repeat($unary, $prod);
                    }
                    
                    $resultType = Type::getArithmeticResultType($leftType, $rightType, '*');
                    if ($resultType === null) {
                        $mensaje = "Operación '*' inválida entre tipos '" . $leftType . "' y '" . $rightType . "'";
                        $this->registrarErrorSemantico($mensaje, $ctx);
                        return null;
                    }
                    
                    return $prod * $unary;
                    
                case '/':
                    $resultType = Type::getArithmeticResultType($leftType, $rightType, '/');
                    if ($resultType === null) {
                        $mensaje = "Operación '/' inválida entre tipos '" . $leftType . "' y '" . $rightType . "'";
                        $this->registrarErrorSemantico($mensaje, $ctx);
                        return null;
                    }
                    
                    if ($unary == 0) {
                        $this->registrarErrorSemantico("División por cero", $ctx);
                        return null;
                    }
                    return $prod / $unary;
                    
                case '%':
                    $resultType = Type::getModuloResultType($leftType, $rightType);
                    if ($resultType === null) {
                        $mensaje = "Operación '%' inválida entre tipos '" . $leftType . "' y '" . $rightType . "'";
                        $this->registrarErrorSemantico($mensaje, $ctx);
                        return null;
                    }
                    
                    if ($unary == 0) {
                        $this->registrarErrorSemantico("Módulo por cero", $ctx);
                        return null;
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
        $value = $this->visit($ctx->unary());
        if (Type::propagateNilUnary($value, '-')) {
            return null;
        }
        return -$value;
    }

    public function visitNotExpression(NotExpressionContext $ctx) {
        $value = $this->visit($ctx->unary());
        if (Type::propagateNilUnary($value, '!')) {
            return null;
        }
        $type = Type::inferType($value);
        if (!Type::canUseLogical($type)) {
            $mensaje = "Operador '!' requiere operando de tipo 'bool', se recibió '" . $type . "'";
            $this->registrarErrorSemantico($mensaje, $ctx);
            return false;
        }
        
        return !$value;
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

    public function visitIdExpression(IdExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->env->get($varName);
        if ($value instanceof Reference) {
            return $value->getValue();
        }
        return $value;
    }
    
    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        return $this->env->get($varName);
    }

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        return $ctx->bool->getText() === 'true';
    }

    public function visitNilExpression(NilExpressionContext $ctx) {
        return null;
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
        $varName = $ctx->ID()->getText();
        $array = $this->env->get($varName);
        if ($array instanceof Reference) {
            $array = $array->getValue();
        }
        foreach ($ctx->e() as $index) {
            $idx = $this->visit($index);
            if (!is_array($array)) {
                throw new Exception("La variable " . $varName . " no es un arreglo");
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
        $tipos = array();
        
        $ids = $ctx->ID();
        $types = $ctx->type();
        
        for ($i = 0; $i < count($ids); $i++) {
            $paramNombre = $ids[$i]->getText();
            $typeCtx = $types[$i];
            $pointerInfo = Type::parsePointerType($typeCtx);
            
            if ($pointerInfo['isPointer']) {
                $tipos[] = [
                    'isPointer' => true,
                    'pointedType' => $pointerInfo['pointedType']
                ];
            } elseif ($typeCtx->INT() !== null) {
                $arrayInfo = Type::parseArrayType($typeCtx);
                $tipos[] = $arrayInfo;
            } else {
                $paramTipo = $typeCtx->getText();
                $tipos[] = $paramTipo;
            }
            
            $params[] = $paramNombre;
        }
        
        return [
            'nombres' => $params,
            'tipos' => $tipos
        ];
    }

    public function visitArgs(ArgsContext $ctx) {
        $args = [];
        foreach ($ctx->e() as $expr) {
            $valor = $this->visit($expr);
            $args[] = $valor;
        }
        
        return $args;
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
        if ($value === null) {
            $this->console .= "nil\n";
            return $value;
        }
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

    private function registrarSimbolo($nombre, $tipo, $valor, $ctx) {
        $linea = $ctx->getStart()->getLine();
        $columna = $ctx->getStart()->getCharPositionInLine();
        
        $simbolo = new Simbolo(
            $nombre,
            $tipo,
            $valor,
            $this->ambitoActual,
            $linea,
            $columna
        );
        
        $this->tablaSimbolos->agregar($simbolo);
    }

    public function obtenerTablaSimbolos() {
        return $this->tablaSimbolos;
    }

    public function obtenerReporteErrores() {
        return $this->reporteErrores;
    }

    private function registrarErrorSemantico($mensaje, $ctx) {
        $linea = $ctx->getStart()->getLine();
        $columna = $ctx->getStart()->getCharPositionInLine();
        $this->reporteErrores->agregarErrorSemantico($mensaje, $linea, $columna);
    }
    
    public function visitModuleFunctionCall(ModuleFunctionCallContext $ctx) {
        $moduleName = $ctx->ID()[0]->getText();
        $functionName = $ctx->ID()[1]->getText();
        
        try {
            $function = Modulos::obtenerFuncion($moduleName, $functionName);
            
            $args = [];
            if ($ctx->args() !== null) {
                $args = $this->visit($ctx->args());
            }
            
            $arity = $function->get_arity();
            if ($arity !== -1 && count($args) !== $arity) {
                $mensaje = "La función " . $moduleName . "." . $functionName . 
                          " espera " . $arity . " argumentos, pero se le dieron " . count($args);
                $this->registrarErrorSemantico($mensaje, $ctx);
                return null;
            }
            
            return $function->invoke($this, $args);
            
        } catch (Exception $e) {
            $this->registrarErrorSemantico($e->getMessage(), $ctx);
            return null;
        }
    }
    
    public function visitModuleFunctionExpression(ModuleFunctionExpressionContext $ctx) {
        $moduleName = $ctx->ID()[0]->getText();
        $functionName = $ctx->ID()[1]->getText();
        
        try {
            $function = Modulos::obtenerFuncion($moduleName, $functionName);
            
            $args = [];
            if ($ctx->args() !== null) {
                $args = $this->visit($ctx->args());
            }
            
            $arity = $function->get_arity();
            if ($arity !== -1 && count($args) !== $arity) {
                $mensaje = "La función " . $moduleName . "." . $functionName . 
                          " espera " . $arity . " argumentos, pero se le dieron " . count($args);
                $this->registrarErrorSemantico($mensaje, $ctx);
                return null;
            }
            
            return $function->invoke($this, $args);
            
        } catch (Exception $e) {
            $this->registrarErrorSemantico($e->getMessage(), $ctx);
            return null;
        }
    }

    public function visitVarDeclarationTypedMultiple(VarDeclarationTypedMultipleContext $ctx) {
        $typeCtx = $ctx->type();
        $ids = $ctx->idList->ID();
        $exprs = $ctx->exprList->e();
        if (count($ids) !== count($exprs)) {
            throw new Exception(
                "Declaración múltiple: se esperaban " . count($ids) . 
                " valores pero se proporcionaron " . count($exprs)
            );
        }
        $isArrayType = ($typeCtx->INT() !== null);
        if ($isArrayType) {
            $arrayInfo = Type::parseArrayType($typeCtx);
            $baseType = $arrayInfo['baseType'];
            $dimensions = $arrayInfo['dimensions'];
            $tipoStr = Type::arrayTypeToString($dimensions, $baseType);
        } else {
            $typeName = $typeCtx->getText();
        }
        for ($i = 0; $i < count($ids); $i++) {
            $varName = $ids[$i]->getText();
            $value = $this->visit($exprs[$i]);
            if ($isArrayType) {
                if (!Type::validateArrayStructure($value, $dimensions, $baseType)) {
                    throw new Exception(
                        "El valor no coincide con el tipo de arreglo '" . $tipoStr . 
                        "' para la variable '" . $varName . "'"
                    );
                }
                $this->env->set($varName, $value);
                $this->registrarSimbolo($varName, $tipoStr, $value, $ctx);
            } else {
                if (!Type::isCompatible($value, $typeName)) {
                    $inferredType = Type::inferType($value);
                    $mensaje = "No se puede asignar un valor de tipo '" . $inferredType . 
                              "' a la variable '" . $varName . "' de tipo '" . $typeName . "'";
                    $this->registrarErrorSemantico($mensaje, $ctx);
                    $value = Type::getDefault($typeName);
                } else {
                    if ($value !== null) {
                        $value = Type::cast($value, $typeName);
                    }
                }
                $this->env->set($varName, $value);
                $this->registrarSimbolo($varName, $typeName, $value, $ctx);
            }
        }
        return null;
    }
    
    public function visitShortVarDeclarationMultiple(ShortVarDeclarationMultipleContext $ctx) {
        $ids = $ctx->idList->ID();
        $exprs = $ctx->exprList->e();
        if (count($exprs) === 1) {
            $expr = $exprs[0];
            $value = $this->visit($expr);
            if (is_array($value) && count($value) === count($ids)) {
                for ($i = 0; $i < count($ids); $i++) {
                    $varName = $ids[$i]->getText();
                    $varValue = $value[$i];
                    $this->env->set($varName, $varValue);
                    $tipoInferido = Type::inferType($varValue);
                    $this->registrarSimbolo($varName, $tipoInferido, $varValue, $ctx);
                }
                return null;
            }
        }
        if (count($ids) !== count($exprs)) {
            throw new Exception(
                "Declaración múltiple: se esperaban " . count($ids) . 
                " valores pero se proporcionaron " . count($exprs)
            );
        }
        for ($i = 0; $i < count($ids); $i++) {
            $varName = $ids[$i]->getText();
            $value = $this->visit($exprs[$i]);
            $this->env->set($varName, $value);
            $tipoInferido = Type::inferType($value);
            $this->registrarSimbolo($varName, $tipoInferido, $value, $ctx);
        }
        return null;
    }

    public function visitTrueExpression(TrueExpressionContext $ctx) {
        return true;
    }
    
    public function visitFalseExpression(FalseExpressionContext $ctx) {
        return false;
    }

    public function visitCompoundAssignmentStatement(CompoundAssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $operator = $ctx->op->getText();
        $rightValue = $this->visit($ctx->e());
        
        try {
            $currentValue = $this->env->get($varName);
        } catch (Exception $e) {
            $this->registrarErrorSemantico("Variable '" . $varName . "' no definida", $ctx);
            return null;
        }
        
        $newValue = null;
        switch ($operator) {
            case '+=':
                $newValue = $currentValue + $rightValue;
                break;
            case '-=':
                $newValue = $currentValue - $rightValue;
                break;
            case '*=':
                $newValue = $currentValue * $rightValue;
                break;
            case '/=':
                if ($rightValue == 0) {
                    $this->registrarErrorSemantico("División por cero", $ctx);
                    return null;
                }
                $newValue = $currentValue / $rightValue;
                break;
            default:
                throw new Exception("Operador compuesto desconocido: " . $operator);
        }
        
        $this->env->assign($varName, $newValue);
        return $newValue;
    }

    public function visitAddressOfExpression(AddressOfExpressionContext $ctx) {
        $unaryCtx = $ctx->unary();
        
        // Solo funciona con variables (IdExpression)
        if ($unaryCtx instanceof PrimaryExpressionContext) {
            $primaryCtx = $unaryCtx->primary();
            
            if ($primaryCtx instanceof IdExpressionContext) {
                $varName = $primaryCtx->ID()->getText();
                
                // Crear y retornar una referencia
                return new Reference($this->env, $varName);
            }
        }
        
        throw new Exception("El operador & solo puede aplicarse a variables");
    }
}