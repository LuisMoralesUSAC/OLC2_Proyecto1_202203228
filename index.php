<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'bootstrap.php';

use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\Error\BailErrorStrategy;
use Antlr\Antlr4\Runtime\Error\Exceptions\ParseCancellationException;
use Antlr\Antlr4\Runtime\Error\Exceptions\InputMismatchException;

$input  = "";
$output = "";
$reporteErrores = null;
$tablaSimbolos = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["accion"])) {
        $accion = $_POST["accion"];
        
        $datosReporteErrores = isset($_POST["datosErrores"]) ? $_POST["datosErrores"] : null;
        $datosTablaSimbolos = isset($_POST["datosTabla"]) ? $_POST["datosTabla"] : null;
        
        if ($accion === "descargarErrores" && $datosReporteErrores) {
            $errores = unserialize(base64_decode($datosReporteErrores));
            
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="reporte_errores.html"');
            
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            echo '<title>Reporte de Errores</title>';
            echo '<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}table{background:white;}</style>';
            echo '</head><body>';
            echo '<h1>Reporte de Errores - Golampi Interpreter</h1>';
            echo '<p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            echo $errores->generarReporteHTML();
            echo '</body></html>';
            exit;
        }
        
        if ($accion === "descargarTabla" && $datosTablaSimbolos) {
            $tabla = unserialize(base64_decode($datosTablaSimbolos));
            
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="tabla_simbolos.html"');
            
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            echo '<title>Tabla de Símbolos</title>';
            echo '<style>body{font-family:Arial,sans-serif;margin:20px;background:#f5f5f5;}table{background:white;}</style>';
            echo '</head><body>';
            echo '<h1>Tabla de Símbolos - Golampi Interpreter</h1>';
            echo '<p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>';
            echo $tabla->generarReporteHTML();
            echo '</body></html>';
            exit;
        }
        
        if ($accion === "descargarCodigo") {
            $codigo = isset($_POST["codigo"]) ? $_POST["codigo"] : "";
            
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="codigo.glp"');
            
            echo $codigo;
            exit;
        }
    }
    
    $input = $_POST["expression"] ?? "";

    if (!empty($input)) {
        try {
            $inputStream = InputStream::fromString($input);
            $lexer  = new GrammarLexer($inputStream);
            $tokens = new CommonTokenStream($lexer);
            $parser = new GrammarParser($tokens);
            $parser->setErrorHandler(new BailErrorStrategy());
            $tree = $parser->p();
            $interpreter = new Interpreter();
            $output = $interpreter->visit($tree);
            $reporteErrores = $interpreter->obtenerReporteErrores();
            $tablaSimbolos = $interpreter->obtenerTablaSimbolos();
            $output = str_replace(["\r\n", "\r"], "\n", $output);
            $output = preg_replace('/^[ \t]+/m', '', $output);
            $output = ltrim($output);

        } catch (ParseCancellationException $e) {
            $cause = $e->getPrevious();

            if ($cause instanceof InputMismatchException) {
                $offending = $cause->getOffendingToken();
                $expected  = $cause->getExpectedTokens();

                $found = $offending ? $offending->getText() : 'EOF';

                $parserObj = $cause->getRecognizer();
                $vocab = $parserObj->getVocabulary();

                $expectedNames = [];
                foreach ($expected->toArray() as $t) {
                    $expectedNames[] = $vocab->getDisplayName($t);
                }
                
                $reporteErrores = new ReporteErrores();
                $reporteErrores->agregarErrorSintactico(
                    sprintf(
                        "Se esperaba %s y se encontró %s",
                        implode(" o ", $expectedNames),
                        $found
                    ),
                    $offending ? $offending->getLine() : 0,
                    $offending ? $offending->getCharPositionInLine() : 0
                );
                
                $output = $reporteErrores->obtenerResumen();
                $tablaSimbolos = new TablaSimbolos(); // Tabla vacía
            } else {
                $output = "Error sintáctico: " . $e->getMessage();
                $reporteErrores = new ReporteErrores();
                $tablaSimbolos = new TablaSimbolos();
            }
        } catch (Exception $e) {
            $output = "Error: " . $e->getMessage();
            $reporteErrores = new ReporteErrores();
            $tablaSimbolos = new TablaSimbolos();
        }
    } else {
        $output = "Por favor ingrese código para parsear.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golampi Interpreter - OLC2 Proyecto 1</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>

<div class="header">
    <h1>🚀 Golampi Interpreter</h1>
    <p class="subtitle">Organización de Lenguajes y Compiladores 2 - USAC</p>
</div>

<div class="main-container">
    <form method="post" id="formPrincipal">
        
        <!-- Barra de herramientas -->
        <div class="toolbar">
            <div class="toolbar-group">
                <button type="button" onclick="nuevoArchivo()" class="btn btn-secondary" title="Nuevo archivo">
                    📄 Nuevo
                </button>
                <button type="button" onclick="document.getElementById('fileInput').click()" class="btn btn-secondary" title="Cargar archivo">
                    📁 Cargar
                </button>
                <button type="button" onclick="guardarArchivo()" class="btn btn-secondary" title="Guardar archivo">
                    💾 Guardar
                </button>
            </div>
            
            <div class="toolbar-group">
                <input type="submit" value="▶️ Ejecutar" class="btn btn-primary">
                <button type="button" onclick="limpiarConsola()" class="btn btn-secondary">
                    🗑️ Limpiar Consola
                </button>
            </div>
        </div>

        <input type="file" id="fileInput" accept=".glp,.txt,.go" style="display: none;" onchange="cargarArchivo(event)">

        <div class="editor-section">
            <h2>📝 Editor de Código</h2>
            <div class="editor-container">
                <div class="line-numbers" id="lineNumbers">1</div>
                <textarea
                    id="editor"
                    name="expression"
                    placeholder="// Escriba su código Golampi aquí..."
                ><?php echo htmlspecialchars($input); ?></textarea>
            </div>
        </div>
    </form>

    <div class="output-section">
        <h2>💻 Consola de Salida</h2>
        <div class="console" id="consola">
            <?php echo htmlspecialchars($output); ?>
        </div>
    </div>

    <?php if ($reporteErrores !== null || $tablaSimbolos !== null): ?>
    <div class="reportes-section">
        <h2>📊 Reportes</h2>
        <div class="reportes-stats">
            <?php if ($reporteErrores !== null): ?>
                <div class="stat-card <?php echo $reporteErrores->hayErrores() ? 'stat-error' : 'stat-success'; ?>">
                    <div class="stat-number"><?php echo $reporteErrores->cantidadErrores(); ?></div>
                    <div class="stat-label">Errores</div>
                </div>
            <?php endif; ?>
            
            <?php if ($tablaSimbolos !== null): ?>
                <div class="stat-card stat-info">
                    <div class="stat-number"><?php echo count($tablaSimbolos->obtenerTodos()); ?></div>
                    <div class="stat-label">Símbolos</div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="reportes-buttons">
            <?php if ($reporteErrores !== null): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="accion" value="descargarErrores">
                    <input type="hidden" name="datosErrores" value="<?php echo base64_encode(serialize($reporteErrores)); ?>">
                    <button type="submit" class="btn btn-report">
                        📄 Descargar Reporte de Errores
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if ($tablaSimbolos !== null): ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="accion" value="descargarTabla">
                    <input type="hidden" name="datosTabla" value="<?php echo base64_encode(serialize($tablaSimbolos)); ?>">
                    <button type="submit" class="btn btn-report">
                        📊 Descargar Tabla de Símbolos
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="footer">
    <p>Proyecto 1 - Intérprete Golampi | 202203228 | OLC2 - 2S 2024</p>
</div>

<script src="/static/script.js"></script>
</body>
</html>