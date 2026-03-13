<?php

class Foreign extends Invocable {
    public $ctx;
    public $closure;
    public $params;
    public $tiposParams;
    public $tiposRetorno;
    
    public function __construct($ctx, $closure, $params, $tiposParams = [], $tiposRetorno = null) {
        $this->ctx = $ctx;
        $this->closure = $closure;
        $this->params = $params;
        $this->tiposParams = $tiposParams;
        $this->tiposRetorno = $tiposRetorno;
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
                $tipoParam = $this->tiposParams[$i];
                if (is_array($tipoParam) && isset($tipoParam['isPointer']) && $tipoParam['isPointer']) {
                    if (!($valorArg instanceof Reference)) {
                        $visitor->console .= "Advertencia: El parámetro '" . $nombreParam . 
                                            "' espera un puntero (usar &)\n";
                    }
                    $newEnv->set($nombreParam, $valorArg);
                } elseif (is_array($tipoParam) && isset($tipoParam['dimensions'])) {
                    if (!Type::validateArrayStructure($valorArg, $tipoParam['dimensions'], $tipoParam['baseType'])) {
                        $tipoEsperado = Type::arrayTypeToString($tipoParam['dimensions'], $tipoParam['baseType']);
                        $visitor->console .= "Advertencia: El parámetro '" . $nombreParam . 
                                            "' espera tipo '" . $tipoEsperado . "'\n";
                    }
                    $newEnv->set($nombreParam, $valorArg);
                } else {
                    $tipoRecibido = Type::inferType($valorArg);
                    if (!Type::isCompatible($valorArg, $tipoParam)) {
                        $visitor->console .= "Advertencia: El parámetro '" . $nombreParam . 
                                            "' espera tipo '" . $tipoParam . 
                                            "' pero recibió '" . $tipoRecibido . "'\n";
                        if ($valorArg !== null) {
                            $valorArg = Type::cast($valorArg, $tipoParam);
                        }
                    }
                    if ($valorArg !== null) {
                        $valorArg = Type::cast($valorArg, $tipoParam);
                    }
                    
                    $newEnv->set($nombreParam, $valorArg);
                }
            } else {
                $newEnv->set($nombreParam, $valorArg);
            }
        }
        $envBeforeCall = $visitor->env;
        $visitor->env = $newEnv;
        $ambitoAnterior = $visitor->ambitoActual;
        $nombreFuncion = $this->ctx->ID()->getText();
        $visitor->ambitoActual = $nombreFuncion;
        $result = $visitor->visit($this->ctx->block());
        if ($result instanceof ReturnType) {
            $valorRetorno = $result->value;
            if ($this->tiposRetorno !== null) {
                if (is_array($this->tiposRetorno) && !isset($this->tiposRetorno['dimensions'])) {
                    if (!is_array($valorRetorno)) {
                        $visitor->console .= "Advertencia: La función debe retornar " . count($this->tiposRetorno) . " valores\n";
                    } else {
                        for ($i = 0; $i < count($this->tiposRetorno); $i++) {
                            $tipoEsperado = $this->tiposRetorno[$i];
                            $valorActual = $valorRetorno[$i];
                            if (is_array($tipoEsperado)) {
                                if (!Type::validateArrayStructure($valorActual, $tipoEsperado['dimensions'], $tipoEsperado['baseType'])) {
                                    $tipoStr = Type::arrayTypeToString($tipoEsperado['dimensions'], $tipoEsperado['baseType']);
                                    $visitor->console .= "Advertencia: El valor " . ($i + 1) . " debe ser de tipo '" . $tipoStr . "'\n";
                                }
                            } else {
                                if (!Type::isCompatible($valorActual, $tipoEsperado)) {
                                    $tipoReal = Type::inferType($valorActual);
                                    $visitor->console .= "Advertencia: El valor " . ($i + 1) . " debe ser de tipo '" . 
                                                        $tipoEsperado . "' pero es '" . $tipoReal . "'\n";
                                } else {
                                    if ($valorActual !== null) {
                                        $valorRetorno[$i] = Type::cast($valorActual, $tipoEsperado);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if (is_array($this->tiposRetorno) && isset($this->tiposRetorno['dimensions'])) {
                        if (!Type::validateArrayStructure($valorRetorno, $this->tiposRetorno['dimensions'], $this->tiposRetorno['baseType'])) {
                            $tipoEsperado = Type::arrayTypeToString($this->tiposRetorno['dimensions'], $this->tiposRetorno['baseType']);
                            $visitor->console .= "Advertencia: La función debe retornar tipo '" . $tipoEsperado . "'\n";
                        }
                    } else {
                        $tipoRetornoReal = Type::inferType($valorRetorno);
                        if (!Type::isCompatible($valorRetorno, $this->tiposRetorno)) {
                            $visitor->console .= "Advertencia: La función debe retornar tipo '" . 
                                                $this->tiposRetorno . "' pero retornó '" . $tipoRetornoReal . "'\n";
                            if ($valorRetorno !== null) {
                                $valorRetorno = Type::cast($valorRetorno, $this->tiposRetorno);
                            }
                        }
                        if ($valorRetorno !== null) {
                            $valorRetorno = Type::cast($valorRetorno, $this->tiposRetorno);
                        }
                    }
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