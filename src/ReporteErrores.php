<?php

class ErrorReportado {
    public $numero;
    public $tipo;
    public $descripcion;
    public $linea;
    public $columna;
    
    public function __construct($numero, $tipo, $descripcion, $linea, $columna) {
        $this->numero = $numero;
        $this->tipo = $tipo;
        $this->descripcion = $descripcion;
        $this->linea = $linea;
        $this->columna = $columna;
    }
}

class ReporteErrores {
    private $errores;
    private $contadorErrores;
    
    public function __construct() {
        $this->errores = [];
        $this->contadorErrores = 0;
    }
    
    public function agregarErrorLexico($descripcion, $linea, $columna) {
        $this->contadorErrores++;
        $error = new ErrorReportado(
            $this->contadorErrores,
            "Léxico",
            $descripcion,
            $linea,
            $columna
        );
        $this->errores[] = $error;
    }

    public function agregarErrorSintactico($descripcion, $linea, $columna) {
        $this->contadorErrores++;
        $error = new ErrorReportado(
            $this->contadorErrores,
            "Sintáctico",
            $descripcion,
            $linea,
            $columna
        );
        $this->errores[] = $error;
    }
    
    public function agregarErrorSemantico($descripcion, $linea, $columna) {
        $this->contadorErrores++;
        $error = new ErrorReportado(
            $this->contadorErrores,
            "Semántico",
            $descripcion,
            $linea,
            $columna
        );
        $this->errores[] = $error;
    }
    
    public function obtenerTodos() {
        return $this->errores;
    }
    
    public function hayErrores() {
        return count($this->errores) > 0;
    }

    public function cantidadErrores() {
        return count($this->errores);
    }

    public function generarReporteHTML() {
        if (!$this->hayErrores()) {
            return '<p style="color: green; font-weight: bold;">No se encontraron errores.</p>';
        }
        
        $html = '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #f44336; color: white;">';
        $html .= '<th>#</th>';
        $html .= '<th>Tipo</th>';
        $html .= '<th>Descripción</th>';
        $html .= '<th>Línea</th>';
        $html .= '<th>Columna</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($this->errores as $error) {
            $html .= '<tr>';
            $html .= '<td>' . $error->numero . '</td>';
            $html .= '<td>' . htmlspecialchars($error->tipo) . '</td>';
            $html .= '<td>' . htmlspecialchars($error->descripcion) . '</td>';
            $html .= '<td>' . $error->linea . '</td>';
            $html .= '<td>' . $error->columna . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        return $html;
    }

    public function generarReporteCSV() {
        $csv = "#,Tipo,Descripción,Línea,Columna\n";
        
        foreach ($this->errores as $error) {
            $csv .= $error->numero . ',';
            $csv .= '"' . $error->tipo . '",';
            $csv .= '"' . str_replace('"', '""', $error->descripcion) . '",';
            $csv .= $error->linea . ',';
            $csv .= $error->columna . "\n";
        }
        
        return $csv;
    }

    public function obtenerResumen() {
        if (!$this->hayErrores()) {
            return "No se encontraron errores.";
        }
        
        $resumen = "Se encontraron " . $this->cantidadErrores() . " errore(s):\n\n";
        
        foreach ($this->errores as $error) {
            $resumen .= sprintf(
                "[%s] Línea %d, Columna %d: %s\n",
                $error->tipo,
                $error->linea,
                $error->columna,
                $error->descripcion
            );
        }
        
        return $resumen;
    }
}