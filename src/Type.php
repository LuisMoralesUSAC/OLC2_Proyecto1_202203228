<?php

class Type {
    const INT32 = 'int32';
    const FLOAT32 = 'float32';
    const BOOL = 'bool';
    const RUNE = 'rune';
    const STRING = 'string';
    const NIL = 'nil';
    const ARRAY = 'array';
    const FUNCTION = 'function';
    
    public static function getDefault($typeName) {
        switch($typeName) {
            case self::INT32:
                return 0;
            case self::FLOAT32:
                return 0.0;
            case self::BOOL:
                return false;
            case self::RUNE:
                return '\u0000';
            case self::STRING:
                return "";
            case self::NIL:
                return null;
            default:
                throw new Exception("Tipo desconocido: " . $typeName);
        }
    }
    
    public static function inferType($value) {
        if ($value === null) {
            return self::NIL;
        }
        if (is_int($value)) {
            return self::INT32;
        }
        if (is_float($value)) {
            return self::FLOAT32;
        }
        if (is_bool($value)) {
            return self::BOOL;
        }
        if (is_string($value)) {
            if (strlen($value) == 1) {
                return self::RUNE;
            }
            return self::STRING;
        }
        if (is_array($value)) {
            return self::ARRAY;
        }
        if ($value instanceof Invocable) {
            return self::FUNCTION;
        }
        
        return self::NIL;
    }
    
    public static function isCompatible($value, $declaredType) {
        $inferredType = self::inferType($value);
        
        if ($inferredType === self::NIL) {
            return true;
        }
        
        if ($declaredType === self::FLOAT32 && $inferredType === self::INT32) {
            return true;
        }

        return $inferredType === $declaredType;
    }
    
    public static function cast($value, $targetType) {
        if ($value === null) {
            return self::getDefault($targetType);
        }
        
        $sourceType = self::inferType($value);
        
        if ($sourceType === $targetType) {
            return $value;
        }
        
        if ($targetType === self::FLOAT32 && $sourceType === self::INT32) {
            return floatval($value);
        }
        
        if ($targetType === self::INT32 && $sourceType === self::FLOAT32) {
            return intval($value);
        }
        
        if ($targetType === self::STRING) {
            if (is_bool($value)) {
                return $value ? "true" : "false";
            }
            return strval($value);
        }
        
        return $value;
    }
    
    public static function getTypeName($value) {
        return self::inferType($value);
    }

    public static function getAdditionResultType($leftType, $rightType) {
        if ($leftType === self::STRING && $rightType === self::STRING) {
            return self::STRING;
        }
        
        $numericTypes = [self::INT32, self::FLOAT32, self::RUNE];
        
        if (in_array($leftType, $numericTypes) && in_array($rightType, $numericTypes)) {
            if ($leftType === self::FLOAT32 || $rightType === self::FLOAT32) {
                return self::FLOAT32;
            }
            return self::INT32;
        }
        
        return null;
    }
    
    public static function getArithmeticResultType($leftType, $rightType, $operator) {
        $numericTypes = [self::INT32, self::FLOAT32, self::RUNE];
        if (!in_array($leftType, $numericTypes) || !in_array($rightType, $numericTypes)) {
            return null;
        }
        
        if ($leftType === self::FLOAT32 || $rightType === self::FLOAT32) {
            return self::FLOAT32;
        }
        
        return self::INT32;
    }

    public static function getModuloResultType($leftType, $rightType) {
        $validTypes = [self::INT32, self::RUNE];
        
        if (in_array($leftType, $validTypes) && in_array($rightType, $validTypes)) {
            return self::INT32;
        }
        
        return null;
    }
    
    public static function getMultiplicationResultType($leftType, $rightType) {
        if ($leftType === self::STRING && $rightType === self::INT32) {
            return self::STRING;
        }
        if ($leftType === self::INT32 && $rightType === self::STRING) {
            return self::STRING;
        }
        
        return self::getArithmeticResultType($leftType, $rightType, '*');
    }

    public static function canCompareRelational($leftType, $rightType) {
        $numericTypes = [self::INT32, self::FLOAT32, self::RUNE];
        
        if (in_array($leftType, $numericTypes) && in_array($rightType, $numericTypes)) {
            return true;
        }
        
        if ($leftType === self::STRING && $rightType === self::STRING) {
            return true;
        }
        
        return false;
    }

    public static function canCompareEquality($leftType, $rightType) {
        $numericTypes = [self::INT32, self::FLOAT32, self::RUNE];
        
        if (in_array($leftType, $numericTypes) && in_array($rightType, $numericTypes)) {
            return true;
        }
        
        if ($leftType === self::STRING && $rightType === self::STRING) {
            return true;
        }
        
        if ($leftType === self::BOOL && $rightType === self::BOOL) {
            return true;
        }
        
        return false;
    }

    public static function canUseLogical($type) {
        return $type === self::BOOL;
    }

    public static function propagateNil($leftValue, $rightValue, $operator) {
        if ($leftValue === null || $rightValue === null) {
            return true;
        }
        return false;
    }
    
    public static function propagateNilUnary($value, $operator) {
        if ($value === null) {
            return true;
        }
        return false;
    }

    public static function parseArrayType($typeCtx) {
        $dimensions = [];
        $current = $typeCtx;
        
        while ($current->INT() !== null) {
            $size = intval($current->INT()->getText());
            $dimensions[] = $size;
            $current = $current->type();
        }
        
        $baseType = $current->getText();
        
        return [
            'baseType' => $baseType,
            'dimensions' => $dimensions
        ];
    }
    
    public static function createArrayWithDefaults($dimensions, $baseType) {
        if (empty($dimensions)) {
            return self::getDefault($baseType);
        }
        
        $size = array_shift($dimensions);
        $array = [];
        
        for ($i = 0; $i < $size; $i++) {
            if (empty($dimensions)) {
                $array[$i] = self::getDefault($baseType);
            } else {
                $array[$i] = self::createArrayWithDefaults($dimensions, $baseType);
            }
        }
        
        return $array;
    }
    
    public static function validateArrayStructure($array, $dimensions, $baseType) {
        if (empty($dimensions)) {
            return self::isCompatible($array, $baseType);
        }
        
        if (!is_array($array)) {
            return false;
        }
        
        $expectedSize = $dimensions[0];
        if (count($array) !== $expectedSize) {
            return false;
        }
        
        $remainingDimensions = array_slice($dimensions, 1);
        
        foreach ($array as $element) {
            if (!self::validateArrayStructure($element, $remainingDimensions, $baseType)) {
                return false;
            }
        }
        
        return true;
    }
    
    public static function arrayTypeToString($dimensions, $baseType) {
        $str = "";
        foreach ($dimensions as $dim) {
            $str .= "[" . $dim . "]";
        }
        $str .= $baseType;
        return $str;
    }
}