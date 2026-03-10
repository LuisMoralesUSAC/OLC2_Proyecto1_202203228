<?php

class Simbolo {
    public $nombre;
    public $tipo;
    public $valor;
    public $ambito;
    public $linea;
    public $columna;
    
    public function __construct($nombre, $tipo, $valor, $ambito, $linea, $columna) {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->ambito = $ambito;
        $this->linea = $linea;
        $this->columna = $columna;
    }
    
    public function obtenerValorComoTexto() {
        if ($this->valor === null) {
            return "nil";
        }
        if (is_bool($this->valor)) {
            return $this->valor ? "true" : "false";
        }
        if (is_array($this->valor)) {
            return json_encode($this->valor);
        }
        if ($this->valor instanceof Invocable) {
            return "—";
        }
        return strval($this->valor);
    }
}