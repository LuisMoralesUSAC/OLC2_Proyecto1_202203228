<?php

/**
 * Clase para representar una referencia (puntero) a una variable
 */
class Reference {
    private $env;
    private $varName;
    
    public function __construct($env, $varName) {
        $this->env = $env;
        $this->varName = $varName;
    }
    
    /**
     * Obtiene el valor de la variable referenciada
     */
    public function getValue() {
        return $this->env->get($this->varName);
    }
    
    /**
     * Establece el valor de la variable referenciada
     */
    public function setValue($value) {
        $this->env->assign($this->varName, $value);
    }
    
    /**
     * Obtiene el nombre de la variable referenciada
     */
    public function getVarName() {
        return $this->varName;
    }
}