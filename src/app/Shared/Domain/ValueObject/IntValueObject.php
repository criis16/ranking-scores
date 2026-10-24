<?php

namespace App\Shared\Domain\ValueObject;

abstract class IntValueObject
{
    public function __construct(protected int $value)
    {
    }

    final public function value(): int
    {
        return $this->value;
    }

    final public function isNegativeValue(): bool
    {
        return $this->value < 0;
    }
}
