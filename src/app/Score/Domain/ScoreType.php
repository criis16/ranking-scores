<?php

namespace App\Score\Domain;

enum ScoreType: string
{
    case Absolute = 'absolute_score';
    case Relative = 'relative_score';

    public static function fromValue(string $type): self
    {
        return self::tryFrom($type)
            ?? throw new \InvalidArgumentException('Score type not allowed: ' . $type);
    }
}
