# Gramática Formal de Golampi


## Convenciones de Notación

- **MAYÚSCULAS**: Tokens terminales (lexer)
- **minúsculas**: Reglas de producción (parser)
- **'literal'**: Símbolos literales
- **|**: Alternativa (OR)
- **?**: Opcional (0 o 1)
- **\***: Cero o más repeticiones
- **+**: Una o más repeticiones
- **( )**: Agrupación
- **#Label**: Etiqueta para alternativa (usado en visitor)

---

## Regla Inicial

```antlr
program
    : stmt* EOF
    ;
```

**Descripción**: Un programa Golampi consiste en cero o más statements seguidos del final del archivo.

---

## Statements (Declaraciones e Instrucciones)

```antlr
stmt
    : ID '.' ID '(' args? ')'                                     # ModuleFunctionCall
    | 'print' '(' e ')'                                           # PrintStatement
    | 'var' idList=idListDecl type '=' exprList=exprListDecl     # VarDeclarationTypedMultiple
    | 'var' ID type '=' e                                         # VarDeclarationTyped
    | 'var' ID type                                               # VarDeclarationTypedEmpty
    | idList=idListDecl ':=' exprList=exprListDecl               # ShortVarDeclarationMultiple
    | ID ':=' e                                                   # ShortVarDeclaration
    | 'const' ID type '=' e                                       # ConstDeclaration
    | 'var' ID '=' e                                              # VarDeclaration
    | ID op=('+=' | '-=' | '*=' | '/=') e                        # CompoundAssignmentStatement
    | ID '=' e                                                    # AssignmentStatement
    | 'if' e block else?                                          # IfStatement
    | 'for' forInit? ';' forCond? ';' forPost? block             # ForStatement
    | 'for' e block                                               # ForConditionStatement
    | 'for' block                                                 # ForInfiniteStatement
    | 'switch' e '{' switchCase* '}'                              # SwitchStatement
    | 'continue'                                                  # ContinueStatement
    | 'break'                                                     # BreakStatement
    | 'return' returnValues?                                      # ReturnStatement
    | 'func' ID '(' params? ')' returnTypes? block               # FunctionDeclaration
    | ID '(' args? ')'                                            # FunctionCallStatement
    | ID ('[' index+=e ']')+ '=' assign=e                        # ArrayAssignmentStatement
    | ID '++'                                                     # IncrementStatement
    | ID '--'                                                     # DecrementStatement
    ;
```

### Descripción de Statements

| Statement | Descripción | Ejemplo |
|-----------|-------------|---------|
| `ModuleFunctionCall` | Llamada a función de módulo | `fmt.Println("Hola")` |
| `PrintStatement` | Función print | `print(x)` |
| `VarDeclarationTypedMultiple` | Declaración múltiple con tipo | `var a, b int32 = 10, 20` |
| `VarDeclarationTyped` | Declaración con tipo e inicialización | `var x int32 = 10` |
| `VarDeclarationTypedEmpty` | Declaración con tipo sin inicialización | `var x int32` |
| `ShortVarDeclarationMultiple` | Declaración corta múltiple | `a, b := 10, 20` |
| `ShortVarDeclaration` | Declaración corta | `x := 10` |
| `ConstDeclaration` | Declaración de constante | `const PI float32 = 3.14` |
| `VarDeclaration` | Declaración sin tipo | `var x = 10` |
| `CompoundAssignmentStatement` | Asignación compuesta | `x += 5` |
| `AssignmentStatement` | Asignación simple | `x = 10` |
| `IfStatement` | Condicional if/else | `if x > 0 { ... }` |
| `ForStatement` | Bucle for tradicional | `for i:=0; i<5; i++ { ... }` |
| `ForConditionStatement` | For con solo condición (while) | `for x < 10 { ... }` |
| `ForInfiniteStatement` | Bucle infinito | `for { ... }` |
| `SwitchStatement` | Estructura switch | `switch x { case 1: ... }` |
| `ContinueStatement` | Continuar iteración | `continue` |
| `BreakStatement` | Romper bucle | `break` |
| `ReturnStatement` | Retorno de función | `return x` |
| `FunctionDeclaration` | Declaración de función | `func suma(a int32) int32 { ... }` |
| `FunctionCallStatement` | Llamada a función | `suma(10)` |
| `ArrayAssignmentStatement` | Asignación a elemento de arreglo | `arr[0] = 10` |
| `IncrementStatement` | Incremento | `x++` |
| `DecrementStatement` | Decremento | `x--` |

---

## Expresiones

### Expresiones Binarias y Precedencia

```antlr
e
    : e op=('*' | '/' | '%') e         # MulDivModExpression
    | e op=('+' | '-') e               # AddSubExpression
    | e op=('<' | '<=' | '>' | '>=') e # RelationalExpression
    | e op=('==' | '!=') e             # EqualityExpression
    | e '&&' e                         # AndExpression
    | e '||' e                         # OrExpression
    | unary                            # UnaryExpression
    ;
```

**Precedencia (de mayor a menor)**:
1. Unario (-, !, &)
2. Multiplicación, División, Módulo (*, /, %)
3. Suma, Resta (+, -)
4. Relacional (<, <=, >, >=)
5. Igualdad (==, !=)
6. AND lógico (&&)
7. OR lógico (||)

---

### Expresiones Unarias

```antlr
unary
    : primary                          # PrimaryExpression
    | '-' unary                        # NegativeExpression
    | '!' unary                        # NotExpression
    | '&' unary                        # AddressOfExpression
    ;
```

| Expresión | Descripción | Ejemplo |
|-----------|-------------|---------|
| `NegativeExpression` | Negación aritmética | `-x` |
| `NotExpression` | Negación lógica | `!bandera` |
| `AddressOfExpression` | Obtener dirección (puntero) | `&variable` |

---

### Expresiones Primarias

```antlr
primary
    : INT                              # IntExpression
    | FLOAT                            # FloatExpression
    | STRING                           # StringExpression
    | RUNE                             # RuneExpression
    | 'true'                           # TrueExpression
    | 'false'                          # FalseExpression
    | 'nil'                            # NilExpression
    | ID                               # IdExpression
    | ID ('[' e ']')+                  # ArrayAccessExpression
    | ID '.' ID '(' args? ')'          # ModuleFunctionExpression
    | ID '(' args? ')'                 # FunctionCallExpression
    | 'print' '(' e ')'                # PrintExpression
    | '(' e ')'                        # ParenExpression
    | arrayLiteral                     # ArrayLiteralExpression
    ;
```

| Expresión | Descripción | Ejemplo |
|-----------|-------------|---------|
| `IntExpression` | Literal entero | `42` |
| `FloatExpression` | Literal flotante | `3.14` |
| `StringExpression` | Literal cadena | `"Hola"` |
| `RuneExpression` | Literal carácter | `'A'` |
| `TrueExpression` | Literal booleano verdadero | `true` |
| `FalseExpression` | Literal booleano falso | `false` |
| `NilExpression` | Valor nulo | `nil` |
| `IdExpression` | Identificador de variable | `x` |
| `ArrayAccessExpression` | Acceso a elemento de arreglo | `arr[0]`, `mat[i][j]` |
| `ModuleFunctionExpression` | Llamada a función de módulo | `fmt.Println(x)` |
| `FunctionCallExpression` | Llamada a función | `suma(10, 20)` |
| `PrintExpression` | Función print como expresión | `print(x)` |
| `ParenExpression` | Expresión entre paréntesis | `(x + y)` |
| `ArrayLiteralExpression` | Literal de arreglo | `[3]int32{1, 2, 3}` |

---

## Tipos de Datos

```antlr
type
    : 'int32'                          # PrimitiveType
    | 'float32'                        # PrimitiveType
    | 'bool'                           # PrimitiveType
    | 'rune'                           # PrimitiveType
    | 'string'                         # PrimitiveType
    | '[' INT ']' type                 # ArrayType
    | '*' type                         # PointerType
    ;
```

### Tipos Primitivos

| Tipo | Descripción | Tamaño | Rango/Valores |
|------|-------------|--------|---------------|
| `int32` | Entero con signo | 32 bits | -2,147,483,648 a 2,147,483,647 |
| `float32` | Punto flotante | 32 bits | IEEE 754 |
| `bool` | Booleano | 1 bit | `true`, `false` |
| `rune` | Carácter único | 8 bits | Cualquier carácter ASCII |
| `string` | Cadena de texto | Variable | Secuencia de caracteres |

### Tipos Compuestos

**Arreglos**: `[tamaño]tipo`
- Ejemplo: `[5]int32` - Arreglo de 5 enteros
- Ejemplo: `[3][3]float32` - Matriz 3x3 de flotantes

**Punteros**: `*tipo`
- Ejemplo: `*int32` - Puntero a entero
- Ejemplo: `*[5]int32` - Puntero a arreglo de 5 enteros

---

## Funciones y Parámetros

### Declaración de Función

```antlr
// Declaración completa de función
'func' ID '(' params? ')' returnTypes? block

// Parámetros
params
    : parameterList
    ;

parameterList
    : ID type (',' ID type)*
    ;
```
---

### Tipos de Retorno

```antlr
returnTypes
    : type                             # SingleReturnType
    | '(' typeList ')'                 # MultipleReturnTypes
    ;

typeList
    : type (',' type)*
    ;
```
---

### Valores de Retorno

```antlr
returnValues
    : e                                # SingleReturnValue
    | exprListReturn                   # MultipleReturnValues
    ;

exprListReturn
    : e (',' e)+
    ;
```
---

### Argumentos

```antlr
args
    : e (',' e)*
    ;
```
---

## Estructuras de Control

### Bloques

```antlr
block
    : '{' stmt* '}'
    ;
```
---

### If/Else

```antlr
// If statement
'if' e block else?

// Else
else
    : 'else' block
    | 'else' 'if' e block else?
    ;
```

---

### For Loop

```antlr
// For tradicional (con inicialización, condición y post)
'for' forInit? ';' forCond? ';' forPost? block

// For condicional (while)
'for' e block

// For infinito
'for' block

// Componentes del for tradicional
forInit
    : ID ':=' e
    | 'var' ID type '=' e
    ;

forCond
    : e
    ;

forPost
    : ID '=' e
    | ID '++'
    | ID '--'
    ;
```
---

### Switch

```antlr
// Switch statement
'switch' e '{' switchCase* '}'

// Casos del switch
switchCase
    : 'case' caseValues ':' stmt*
    | 'default' ':' stmt*
    ;

caseValues
    : e (',' e)*
    ;
```
---

## Arreglos

### Literales de Arreglos

```antlr
arrayLiteral
    : '[' INT ']' type '{' arrayElements? '}'
    ;

arrayElements
    : arrayElement (',' arrayElement)*
    ;

arrayElement
    : e                                # SimpleArrayElement
    | '{' arrayElements '}'            # NestedArrayElement
    ;
```
---

## Declaraciones Múltiples

```antlr
// Lista de identificadores
idListDecl
    : ID (',' ID)+
    ;

// Lista de expresiones
exprListDecl
    : e (',' e)*
    ;
```
---

## Tokens Léxicos

### Palabras Reservadas

```antlr
'func'      // Declaración de función
'var'       // Declaración de variable
'const'     // Declaración de constante
'if'        // Condicional
'else'      // Alternativa de condicional
'for'       // Bucle
'switch'    // Estructura switch
'case'      // Caso de switch
'default'   // Caso por defecto
'break'     // Romper bucle/switch
'continue'  // Continuar iteración
'return'    // Retorno de función
'true'      // Literal booleano verdadero
'false'     // Literal booleano falso
'nil'       // Valor nulo
'print'     // Función print
```

---

### Tipos de Datos (Palabras Reservadas)

```antlr
'int32'     // Entero de 32 bits
'float32'   // Flotante de 32 bits
'bool'      // Booleano
'rune'      // Carácter
'string'    // Cadena
```

---

### Operadores

#### Aritméticos
```antlr
'+'         // Suma
'-'         // Resta
'*'         // Multiplicación
'/'         // División
'%'         // Módulo
```

#### Relacionales
```antlr
'=='        // Igual a
'!='        // Diferente de
'<'         // Menor que
'<='        // Menor o igual que
'>'         // Mayor que
'>='        // Mayor o igual que
```

#### Lógicos
```antlr
'&&'        // AND lógico
'||'        // OR lógico
'!'         // NOT lógico
```

#### Asignación
```antlr
'='         // Asignación simple
':='        // Declaración corta
'+='        // Asignación con suma
'-='        // Asignación con resta
'*='        // Asignación con multiplicación
'/='        // Asignación con división
```

#### Incremento/Decremento
```antlr
'++'        // Incremento
'--'        // Decremento
```

#### Punteros
```antlr
'&'         // Dirección de (address-of)
'*'         // Puntero a (en declaración de tipo)
```

---

### Literales

```antlr
// Números
FLOAT
    : [0-9]+ '.' [0-9]+
    ;

INT
    : [0-9]+
    ;

// Cadenas
STRING
    : '"' (~["\r\n])* '"'
    ;

// Caracteres
RUNE
    : '\'' . '\''
    ;

// Identificadores
ID
    : [a-zA-Z_][a-zA-Z0-9_]*
    ;
```

**Ejemplos**:
```
INT:    0, 42, 1000
FLOAT:  3.14, 0.5, 100.0
STRING: "Hola", "Mundo", ""
RUNE:   'A', 'z', '5', ' '
ID:     x, nombre, edad_persona, _temp
```

---

### Delimitadores

```antlr
'('         // Paréntesis izquierdo
')'         // Paréntesis derecho
'{'         // Llave izquierda
'}'         // Llave derecha
'['         // Corchete izquierdo
']'         // Corchete derecho
','         // Coma
':'         // Dos puntos
';'         // Punto y coma
'.'         // Punto
```

---

### Comentarios y Espacios en Blanco

```antlr
// Comentario de línea
'//' ~[\r\n]*                -> skip

// Comentario de bloque
'/*' .*? '*/'                -> skip

// Espacios en blanco
[ \t\r\n]+                   -> skip
```
---