<?php
class Modulos {
    public static function obtenerFuncion($modulo, $funcion) {
        if ($modulo === "fmt") {
            return self::obtenerFuncionFmt($funcion);
        }
        
        throw new Exception("Módulo '" . $modulo . "' no existe");
    }
    
    private static function obtenerFuncionFmt($funcion) {
        if ($funcion === "Println") {
            return new class extends Invocable {
                public function get_arity() {
                    return -1;
                }
                
                public function invoke($visitor, $args) {
                    $output = "";
                    for ($i = 0; $i < count($args); $i++) {
                        $valor = $args[$i];
                        
                        if ($valor === null) {
                            $output .= "nil";
                        } elseif (is_bool($valor)) {
                            $output .= $valor ? "true" : "false";
                        } elseif (is_array($valor)) {
                            $output .= json_encode($valor);
                        } elseif ($valor instanceof Invocable) {
                            $output .= "function";
                        } else {
                            $output .= strval($valor);
                        }
                        
                        if ($i < count($args) - 1) {
                            $output .= " ";
                        }
                    }
                    $output .= "\n";
                    $visitor->console .= $output;
                    return null;
                }
            };
        }
        throw new Exception("Función 'fmt." . $funcion . "' no existe");
    }
}