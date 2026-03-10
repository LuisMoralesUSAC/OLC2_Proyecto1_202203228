<?php

class Foreign extends Invocable {
    public $ctx;
    public $closure;
    public $params;
    public $tiposParams;
    public $tipoRetorno;
    
    public function __construct($ctx, $closure, $params, $tiposParams = [], $tipoRetorno = null) {
        $this->ctx = $ctx;
        $this->closure = $closure;
        $this->params = $params;
        $this->tiposParams = $tiposParams;
        $this->tipoRetorno = $tipoRetorno;
    }
    
    public function get_arity() {
        return count($this->params);
    }
    
    public function invoke($visitor, $args){
        $newEnv = new Environment($this->closure);
        
        for ($i = 0; $i < count($this->params); $i++) {
            $nombreParam = $this->params[$i];
            $valorArg = $args[$i];
            
            if ($i < count($this->tiposParams)) {
                $tipoEsperado = $this->tiposParams[$i];
                $tipoRecibido = Type::inferType($valorArg);
                
                if (!Type::isCompatible($valorArg, $tipoEsperado)) {
                    $visitor->console .= "Advertencia: El parámetro '" . $nombreParam . 
                                        "' espera tipo '" . $tipoEsperado . 
                                        "' pero recibió '" . $tipoRecibido . "'\n";
                    if ($valorArg !== null) {
                        $valorArg = Type::cast($valorArg, $tipoEsperado);
                    }
                }
                if ($valorArg !== null) {
                    $valorArg = Type::cast($valorArg, $tipoEsperado);
                }
            }
            
            $newEnv->set($nombreParam, $valorArg);
        }
        
        $envBeforeCall = $visitor->env;
        $visitor->env = $newEnv;
        $ambitoAnterior = $visitor->ambitoActual;
        $nombreFuncion = $this->ctx->ID()->getText();
        $visitor->ambitoActual = $nombreFuncion;
        $result = $visitor->visit($this->ctx->block());
        
        if ($result instanceof ReturnType) {
            $valorRetorno = $result->value;
            if ($this->tipoRetorno !== null) {
                $tipoRetornoReal = Type::inferType($valorRetorno);
                
                if (!Type::isCompatible($valorRetorno, $this->tipoRetorno)) {
                    $visitor->console .= "Advertencia: La función debe retornar tipo '" . 
                                        $this->tipoRetorno . "' pero retornó '" . $tipoRetornoReal . "'\n";
                    if ($valorRetorno !== null) {
                        $valorRetorno = Type::cast($valorRetorno, $this->tipoRetorno);
                    }
                }
                if ($valorRetorno !== null) {
                    $valorRetorno = Type::cast($valorRetorno, $this->tipoRetorno);
                }
            }
            
            $visitor->env = $envBeforeCall;
            $visitor->ambitoActual = $ambitoAnterior;
            return $valorRetorno;
        }
        
        $visitor->env = $envBeforeCall;
        $visitor->ambitoActual = $ambitoAnterior;
        return $result;
    }
}