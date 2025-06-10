<?php

namespace App\Domains\Core\DTOs;

class Validators
{
    public static function notEmptyString(string $value, string $fieldName): void
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException("$fieldName cannot be empty");
        }
    }

    public static function instanceOf(object|null $value, string $className, string $fieldName): void
    {
        if ($value !== null && !($value instanceof $className)) {
            throw new \InvalidArgumentException("$fieldName must be an instance of $className");
        }
    }
}