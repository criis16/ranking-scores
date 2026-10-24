<?php

namespace App\Score\Domain;

enum RelativeScoreOperation: string
{
    case Add = '+';
    case Subtract = '-';

    public static function fromValue(string $operation): self
    {
        return self::tryFrom($operation)
            ?? throw new \InvalidArgumentException('Relative score operation not allowed: ' . $operation);
    }
}
