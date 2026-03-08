grammar Grammar;

// Program
p
    : stmt* EOF                            # Program
    ;

type
    : 'int32'
    | 'float32'
    | 'bool'
    | 'rune'
    | 'string'
    ;

stmt
    : 'print' '(' e ')'                    # PrintStatement
    | 'var' ID type '=' e                  # VarDeclarationTyped
    | 'var' ID type                        # VarDeclarationTypedEmpty
    | ID ':=' e                            # ShortVarDeclaration
    | 'const' ID type '=' e                # ConstDeclaration
    | 'var' ID '=' e                       # VarDeclaration    
    | ID '=' e                             # AssignmentStatement
    | 'if' '(' e ')' block else?           # IfStatement
    | 'while' '(' e ')' block              # WhileStatement
    | 'continue'                           # ContinueStatement
    | 'break'                              # BreakStatement    
    | 'return' e?                          # ReturnStatement
    | 'func' ID '(' params? ')' block      # FunctionDeclaration
    | ID '(' args? ')'                     # FunctionCallStatement
    | ID ('[' index+=e ']')+ '=' assign=e  # ArrayAssignmentStatement
    ;

block
    : '{' stmt* '}'                        # BlockStatement
    ;

else
    : 'else' block
    ;

/*
    * Expressions, precedence levels
    1. Equality: ==
    2. Inequality: >, <
    3. Addition: +, -
    4. Multiplication: *, /
    5. Unary: -
    6. Primary: INT, ID, (e)
*/

e    
    : logicalOr                       
    ;

logicalOr
    : logicalOr '||' logicalAnd        # OrExpression
    | logicalAnd                       # OrExpression
    ;

logicalAnd
    : logicalAnd '&&' eq               # AndExpression
    | eq                               # AndExpression
    ;

eq
    : left=rel (op=('=='|'!=') right=rel)?  # EqualityExpression
    ;

rel
    : left=add (op=('>'|'>='|'<'|'<=') right=add)? # RelationalExpression    
    ;

add 
    : add op=('+' | '-') prod          # AddExpression
    | prod                             # AddExpression
    ;

prod
    : prod op=('*' | '/' | '%') unary  # ProductExpression
    | unary                            # ProductExpression
    ;

unary
    : primary                          # PrimaryExpression
    | '-' unary                        # NegativeExpression
    | '!' unary                        # NotExpression
    ;

primary    
    : '(' e ')'                        # GroupedExpression   
    | FLOAT                            # FloatExpression
    | INT                              # IntExpression
    | ID                               # ReferenceExpression
    | bool=('true'|'false')            # BoolExpression
    | ID '(' args? ')'                 # FunctionCallExpression
    | '[' e (',' e)* ']'               # ArrayExpression
    | ID ('[' e ']')+                  # ArrayAccessExpression
    | STRING                           # StringExpression
    | RUNE                             # RuneExpression
    ;

params
    : ID (',' ID)*                      # ParameterList
    ;

args
    : e (',' e)*                        # ArgumentList
    ;

// Lexer rules
FLOAT : [0-9]+ '.' [0-9]+ ;
INT   : [0-9]+ ;
STRING : '"' (~["\r\n] | '\\' .)* '"' ;
RUNE   : '\'' (~['\r\n] | '\\' .)* '\'' ;
ID    : [a-zA-Z_][a-zA-Z0-9_]* ;

LINE_COMMENT  : '//' ~[\r\n]* -> skip ;
BLOCK_COMMENT : '/*' .*? '*/' -> skip ;

WS  : [ \t\r\n]+ -> skip ;
