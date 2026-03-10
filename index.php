<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parser Playground</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>

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
            echo '<style>body{font-family:Arial,sans-serif;margin:20px;}</style>';
            echo '</head><body>';
            echo '<h1>Reporte de Errores - Golampi Interpreter</h1>';
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
            echo '<style>body{font-family:Arial,sans-serif;margin:20px;}</style>';
            echo '</head><body>';
            echo '<h1>Tabla de Símbolos - Golampi Interpreter</h1>';
            echo $tabla->generarReporteHTML();
            echo '</body></html>';
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
                $tablaSimbolos = new TablaSimbolos();
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
    <title>Golampi Interpreter</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>

<h2>Intérprete de Golampi</h2>

<form method="post" id="formPrincipal">
    <div class="editor-container">
        <div class="line-numbers" id="lineNumbers">1</div>
        <textarea
            id="editor"
            name="expression"
            placeholder="Escriba su código aquí..."
        ><?php echo htmlspecialchars($input); ?></textarea>
    </div>

    <div class="button-container">
        <input type="submit" value="Ejecutar / Analizar">
        <button type="button" onclick="limpiarEditor()">Nuevo / Limpiar</button>
        <button type="button" onclick="limpiarConsola()">Limpiar Consola</button>
    </div>
</form>

<h2>Consola de Salida:</h2>

<div class="console" id="consola">
    <?php echo htmlspecialchars($output); ?>
</div>

<?php if ($reporteErrores !== null || $tablaSimbolos !== null): ?>
<h2>Reportes:</h2>

<div class="reportes-container">
    <?php if ($reporteErrores !== null): ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="accion" value="descargarErrores">
            <input type="hidden" name="datosErrores" value="<?php echo base64_encode(serialize($reporteErrores)); ?>">
            <button type="submit" class="btn-reporte">📄 Descargar Reporte de Errores</button>
        </form>
    <?php endif; ?>
    
    <?php if ($tablaSimbolos !== null): ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="accion" value="descargarTabla">
            <input type="hidden" name="datosTabla" value="<?php echo base64_encode(serialize($tablaSimbolos)); ?>">
            <button type="submit" class="btn-reporte">📊 Descargar Tabla de Símbolos</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<script src="/static/script.js"></script>
<script>
function limpiarEditor() {
    document.getElementById('editor').value = '';
    document.getElementById('consola').textContent = '';
}

function limpiarConsola() {
    document.getElementById('consola').textContent = '';
}
</script>
</body>
</html>
