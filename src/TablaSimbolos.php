<?php

class TablaSimbolos {
    private $simbolos;
    
    public function __construct() {
        $this->simbolos = [];
    }
    
    public function agregar(Simbolo $simbolo) {
        $this->simbolos[] = $simbolo;
    }
    
    public function obtenerTodos() {
        return $this->simbolos;
    }
    
    public function generarReporteHTML() {
        $html = '<table border="1" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #4CAF50; color: white;">';
        $html .= '<th>Identificador</th>';
        $html .= '<th>Tipo</th>';
        $html .= '<th>Ámbito</th>';
        $html .= '<th>Valor</th>';
        $html .= '<th>Línea</th>';
        $html .= '<th>Columna</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($this->simbolos as $simbolo) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($simbolo->nombre) . '</td>';
            $html .= '<td>' . htmlspecialchars($simbolo->tipo) . '</td>';
            $html .= '<td>' . htmlspecialchars($simbolo->ambito) . '</td>';
            $html .= '<td>' . htmlspecialchars($simbolo->obtenerValorComoTexto()) . '</td>';
            $html .= '<td>' . $simbolo->linea . '</td>';
            $html .= '<td>' . $simbolo->columna . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        return $html;
    }
    
    public function generarReporteCSV() {
        $csv = "Identificador,Tipo,Ámbito,Valor,Línea,Columna\n";
        
        foreach ($this->simbolos as $simbolo) {
            $csv .= '"' . $simbolo->nombre . '",';
            $csv .= '"' . $simbolo->tipo . '",';
            $csv .= '"' . $simbolo->ambito . '",';
            $csv .= '"' . $simbolo->obtenerValorComoTexto() . '",';
            $csv .= $simbolo->linea . ',';
            $csv .= $simbolo->columna . "\n";
        }
        
        return $csv;
    }
}