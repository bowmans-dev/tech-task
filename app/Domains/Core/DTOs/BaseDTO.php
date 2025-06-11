<?php

namespace App\Domains\Core\DTOs;

abstract class BaseDTO
{
    public function __construct()
    {
        $this->validate();
    }


    protected function validate(): void
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            
            $property->setAccessible(true);
            $value = $property->getValue($this);
            $type = $property->getType();

            if ($type) {
                if (!$this->validateType($value, $type)) {
                    throw new \InvalidArgumentException("Property {$property->getName()} must be of type {$type->getName()}");
                }
                if ($value === null && !$type->allowsNull()) {
                    throw new \InvalidArgumentException("Property {$property->getName()} cannot be null");
                }
            }
        }
    }


    protected function validateType(mixed $value, \ReflectionType $type): bool
    {
        if ($value === null) {
            return $type->allowsNull();
        }

        $typeName = $type->getName();

        return match ($typeName) {
            'string' => is_string($value),
            'int' => is_int($value),
            'bool' => is_bool($value),
            default => $value instanceof $typeName,
        };
    }
}