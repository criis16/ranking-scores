<?php

namespace App\Score\Domain;

abstract class Score
{
    private const int ZERO_POINTS = 0;

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
