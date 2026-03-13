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
    | '[' INT ']' type
    | '*' type
    ;

// Statements
stmt
    : ID '.' ID '(' args? ')'              # ModuleFunctionCall
    | 'print' '(' e ')'                    # PrintStatement
    | 'var' idList=idListDecl type '=' exprList=exprListDecl  # VarDeclarationTypedMultiple
    | 'var' ID type '=' e                  # VarDeclarationTyped
    | 'var' ID type                        # VarDeclarationTypedEmpty
    | idList=idListDecl ':=' exprList=exprListDecl            # ShortVarDeclarationMultiple
    | ID ':=' e                            # ShortVarDeclaration
    | 'const' ID type '=' e                # ConstDeclaration
    | 'var' ID '=' e                       # VarDeclaration
    | ID op=('+=' | '-=' | '*=' | '/=') e  # CompoundAssignmentStatement
    | ID '=' e                             # AssignmentStatement
    | 'if' e block else?                   # IfStatement
    | 'for' forInit? ';' forCond? ';' forPost? block  # ForStatement
    | 'for' e block                        # ForConditionStatement
    | 'for' block                          # ForInfiniteStatement
    | 'switch' e '{' switchCase* '}'       # SwitchStatement
    | 'continue'                           # ContinueStatement
    | 'break'                              # BreakStatement
    | 'return' returnValues?               # ReturnStatement
    | 'func' ID '(' params? ')' returnTypes? block  # FunctionDeclaration
    | ID '(' args? ')'                     # FunctionCallStatement
    | ID ('[' index+=e ']')+ '=' assign=e  # ArrayAssignmentStatement
    | ID '++'                              # IncrementStatement
    | ID '--'                              # DecrementStatement
    ;

block
    : '{' stmt* '}'                        # BlockStatement
    ;

else
    : 'else' block
    ;

switchCase
    : 'case' e (',' e)* ':'  stmt*         # CaseClause
    | 'default' ':' stmt*                  # DefaultClause
    ;
    
forInit
    : 'var' ID type '=' e                  # ForInitVarTyped
    | ID ':=' e                            # ForInitShort
    | ID '=' e                             # ForInitAssign
    ;

forCond
    : e
    ;

forPost
    : ID '=' e                             # ForPostAssign
    | ID '++'                              # ForPostIncrement
    | ID '--'                              # ForPostDecrement
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
    | '&' unary                        # AddressOfExpression
    ;

primary
    : INT                                  # IntExpression
    | FLOAT                                # FloatExpression
    | STRING                               # StringExpression
    | RUNE                                 # RuneExpression
    | ID                                   # IdExpression
    | 'true'                               # TrueExpression
    | 'false'                              # FalseExpression
    | 'nil'                                # NilExpression
    | 'print' '(' e ')'                    # PrintExpression
    | ID '.' ID '(' args? ')'              # ModuleFunctionExpression
    | ID '(' args? ')'                     # FunctionCallExpression
    | ID ('[' index+=e ']')+               # ArrayAccessExpression
    | arrayLiteral                         # ArrayLiteralExpression
    | '(' e ')'                            # ParenExpression
    ;

params
    : ID type (',' ID type)*               # ParameterList
    ;

args
    : e (',' e)*                        # ArgumentList
    ;

arrayLiteral
    : '[' INT ']' type '{' arrayElements? '}'
    ;

arrayElements
    : arrayElement (',' arrayElement)*
    ;

arrayElement
    : e                                     # SimpleArrayElement
    | '{' arrayElements '}'                 # NestedArrayElement
    ;

idListDecl
    : ID (',' ID)+
    ;

exprListDecl
    : e (',' e)*
    ;

returnTypes
    : type                                  # SingleReturnType
    | '(' typeList ')'                      # MultipleReturnTypes
    ;

typeList
    : type (',' type)*
    ;

returnValues
    : e                                     # SingleReturnValue
    | exprListReturn                        # MultipleReturnValues
    ;

exprListReturn
    : e (',' e)+
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
