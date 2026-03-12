<?php 

class Time extends Invocable {
    public function get_arity() {
        return 0;
    }
    public function invoke($visitor, $args) {
        date_default_timezone_set("UTC");
        return date("d-M-Y H:i:s");
    }
}

class Len extends Invocable {
    public function get_arity() {
        return 1;
    }
    public function invoke($visitor, $args) {
        $value = $args[0];
        
        if (is_string($value)) {
            return strlen($value);
        }
        
        if (is_array($value)) {
            return count($value);
        }
        
        throw new Exception("len() solo acepta strings o arreglos");
    }
}

class TypeOf extends Invocable {
    public function get_arity() {
        return 1;
    }
    public function invoke($visitor, $args) {
        $value = $args[0];
        
        if ($value === null) {
            return "nil";
        }
        
        if (is_int($value)) {
            return "int32";
        }
        
        if (is_float($value)) {
            return "float32";
        }
        
        if (is_bool($value)) {
            return "bool";
        }
        
        if (is_string($value)) {
            if (strlen($value) == 1) {
                return "rune";
            }
            return "string";
        }
        
        if (is_array($value)) {
            return $this->getArrayType($value);
        }
        
        if ($value instanceof Invocable) {
            return "function";
        }
        
        return "unknown";
    }
    
    private function getArrayType($array) {
        if (!is_array($array) || empty($array)) {
            return "array";
        }
        
        $size = count($array);
        $firstElement = reset($array);
        
        if (is_array($firstElement)) {
            $innerType = $this->getArrayType($firstElement);
            return "[" . $size . "]" . $innerType;
        }
        
        $baseType = Type::inferType($firstElement);
        return "[" . $size . "]" . $baseType;
    }
}

class Now extends Invocable {
    public function get_arity() {
        return 0;
    }
    public function invoke($visitor, $args) {
        date_default_timezone_set("America/Guatemala");
        return date("Y-m-d H:i:s");
    }
}

class Substr extends Invocable {
    public function get_arity() {
        return 3;
    }
    public function invoke($visitor, $args) {
        $texto = $args[0];
        $inicio = $args[1];
        $longitud = $args[2];
        
        if (!is_string($texto)) {
            throw new Exception("substr() requiere un string como primer argumento");
        }
        
        if (!is_int($inicio)) {
            throw new Exception("substr() requiere un entero como segundo argumento (inicio)");
        }
        
        if (!is_int($longitud)) {
            throw new Exception("substr() requiere un entero como tercer argumento (longitud)");
        }
        
        if ($inicio < 0) {
            throw new Exception("substr(): el inicio no puede ser negativo");
        }
        
        if ($inicio >= strlen($texto)) {
            return "";
        }
        
        if ($longitud < 0) {
            throw new Exception("substr(): la longitud no puede ser negativa");
        }
        
        return substr($texto, $inicio, $longitud);
    }
}

return $embeded = array(
    "time" => new Time(),
    "len" => new Len(),
    "typeOf" => new TypeOf(),
    "now" => new Now(),
    "substr" => new Substr()
);