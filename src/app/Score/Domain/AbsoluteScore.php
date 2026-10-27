<?php

namespace App\Score\Domain;

final class AbsoluteScore extends Score
{
    public function type(): ScoreType
    {
        return ScoreType::Absolute;
    }

    public static function initialize(): self
    {
        return new self(ScorePoints::initialize());
    }

    public function applyTo(Score $score): self
    {
        return new self(
            $this->points()->add($score->points())
        );
    }
}
