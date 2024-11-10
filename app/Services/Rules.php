<?php

namespace App\Services;

class Rules
{
    public static function required($value)
    {
        return empty($value) ? 'Это поле обязательно для заполнения.' : null;
    }

    public static function maxLength($value, $maxLength)
    {
        return strlen($value) > $maxLength ? "Длина поля не должна превышать {$maxLength} символов." : null;
    }

    public static function numeric($value)
    {
        return !is_numeric($value) ? 'Значение должно быть числом.' : null;
    }

    public static function between($value, $min, $max)
    {
        if (!is_numeric($value)) {
            return 'Значение должно быть числом.';
        }
        return ($value < $min || $value > $max) ? "Значение должно быть в диапазоне от {$min} до {$max}." : null;
    }

    public static function regex($value, $pattern, $message)
    {
        return !preg_match($pattern, $value) ? $message : null;
    }
}
