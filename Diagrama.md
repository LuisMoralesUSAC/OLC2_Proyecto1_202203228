# Diagramas - Intérprete Golampi

## Diagrama de Clases

### Clases Principales del Intérprete

```mermaid
classDiagram
    class Interpreter {
        +Environment env
        +TablaSimbolos tablaSimbolos
        +ReporteErrores reporteErrores
        +string console
        +string ambitoActual
        +visit(ctx) mixed
        +visitProgram(ctx)
        +visitFunctionDeclaration(ctx)
        +visitVarDeclaration(ctx)
        +visitIfStatement(ctx)
        +visitForStatement(ctx)
        +visitReturnStatement(ctx)
        +visitBinaryExpression(ctx)
        +visitArrayAccess(ctx)
        +registrarSimbolo(nombre, tipo, valor, ctx)
        +registrarErrorSemantico(mensaje, ctx)
    }

    class Environment {
        -array values
        -Environment enclosing
        +__construct(enclosing)
        +set(name, value)
        +get(name)
        +assign(name, value)
        +exists(name)
        +get_ref(name)
    }

    class TablaSimbolos {
        -array simbolos
        +agregar(simbolo)
        +obtenerTodos()
        +generarReporteHTML()
        +generarReporteCSV()
        +limpiar()
    }

    class Simbolo {
        +string nombre
        +string tipo
        +mixed valor
        +string ambito
        +int linea
        +int columna
        +__construct(nombre, tipo, valor, ambito, linea, columna)
    }

    class ReporteErrores {
        -array erroresLexicos
        -array erroresSintacticos
        -array erroresSemanticos
        +agregarErrorLexico(linea, columna, mensaje)
        +agregarErrorSintactico(linea, columna, mensaje)
        +agregarErrorSemantico(linea, columna, mensaje)
        +generarReporteHTML()
        +generarReporteCSV()
        +obtenerResumen()
        +hayErrores()
    }

    class Type {
        +const INT32
        +const FLOAT32
        +const BOOL
        +const RUNE
        +const STRING
        +const NIL
        +const ARRAY
        +const FUNCTION
        +inferType(value) string
        +isCompatible(value, type) bool
        +cast(value, targetType) mixed
        +getDefault(type) mixed
        +getAdditionResultType(type1, type2) string
        +getArithmeticResultType(type1, type2) string
        +getModuloResultType(type1, type2) string
        +parseArrayType(ctx) array
        +createArrayWithDefaults(dimensions, baseType) array
        +validateArrayStructure(array, dimensions, baseType) bool
        +arrayTypeToString(dimensions, baseType) string
        +parsePointerType(typeCtx) array
        +propagateNil(type1, type2) string
        +propagateNilUnary(type) string
    }

    class FlowTypes {
        <<abstract>>
    }

    class ReturnType {
        +mixed value
        +__construct(value)
    }

    class BreakType {
        +__construct()
    }

    class ContinueType {
        +__construct()
    }

    Interpreter --> Environment
    Interpreter --> TablaSimbolos
    Interpreter --> ReporteErrores
    Interpreter --> Type
    TablaSimbolos --> Simbolo
    FlowTypes <|-- ReturnType
    FlowTypes <|-- BreakType
    FlowTypes <|-- ContinueType
```

## Diagramas de Flujo de Procesamiento

### Flujo General de Ejecución

```mermaid
flowchart TD
    A[Inicio] --> B[Leer código fuente]
    B --> C[Análisis Léxico - Lexer]
    C --> D{¿Errores léxicos?}
    D -->|Sí| E[Generar reporte de errores]
    D -->|No| F[Análisis Sintáctico - Parser]
    F --> G{¿Errores sintácticos?}
    G -->|Sí| E
    G -->|No| H[Generar AST]
    H --> I[Fase 1: Registrar funciones]
    I --> J[Fase 2: Validar código fuera de funciones]
    J --> K{¿Existe main?}
    K -->|No| E
    K -->|Sí| L{¿main bien declarada?}
    L -->|No| E
    L -->|Sí| M[Fase 3: Ejecutar main]
    M --> N[Interpretación con Visitor]
    N --> O{¿Error semántico?}
    O -->|Sí| P[Registrar error y continuar]
    O -->|No| Q[Actualizar entorno]
    P --> Q
    Q --> R[Registrar en tabla de símbolos]
    R --> S{¿Fin del programa?}
    S -->|No| N
    S -->|Sí| T[Generar reportes]
    T --> U[Mostrar salida en consola]
    U --> V[Fin]
    E --> V
```

## Tabla de Símbolos

### Estructura de la Tabla de Símbolos

```mermaid
classDiagram
    class TablaSimbolos {
        -simbolos: Array~Simbolo~
        +agregar(simbolo)
        +obtenerTodos()
        +generarReporteHTML()
        +generarReporteCSV()
    }
    
    class Simbolo {
        +nombre: string
        +tipo: string
        +valor: mixed
        +ambito: string
        +linea: int
        +columna: int
    }
    
    TablaSimbolos "1" --> "*" Simbolo : contiene
```