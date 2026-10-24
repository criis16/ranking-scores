<?php

namespace App\Score\Domain;

use App\Shared\Domain\ValueObject\IntValueObject;

final class ScorePoints extends IntValueObject
{
    private const int ZERO_POINTS = 0;

    public function __construct(int $points)
    {
        $points = $this->isNegativeValue() ? self::ZERO_POINTS : $points;
        parent::__construct($points);
    }

    public static function initialize(): self
    {
        return new self(self::ZERO_POINTS);
    }

    public function add(self $other): self
    {
        return new self($this->value() + $other->value());
    }

    public function subtract(self $other): self
    {
        return new self($this->value() - $other->value());
    }
}
