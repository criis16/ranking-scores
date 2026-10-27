<?php

namespace App\Score\Domain;

abstract class Score
{
    public function __construct(private readonly ScorePoints $points)
    {
    }

    final public function points(): ScorePoints
    {
        return $this->points;
    }

    abstract public function type(): ScoreType;

    abstract public function applyTo(Score $score): Score;
}
