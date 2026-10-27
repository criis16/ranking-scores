<?php

namespace App\Score\Domain;

final class RelativeScore extends Score
{
    public function __construct(
        ScorePoints                             $points,
        private readonly RelativeScoreOperation $operation
    )
    {
        parent::__construct($points);
    }

    public function type(): ScoreType
    {
        return ScoreType::Relative;
    }

    public function operation(): RelativeScoreOperation
    {
        return $this->operation;
    }

    public function applyTo(Score $score): self
    {
        return new self(
            match ($this->operation) {
                RelativeScoreOperation::Add => $score->points()->add($this->points()),
                RelativeScoreOperation::Subtract => $score->points()->subtract($this->points()),
            },
            $this->operation
        );
    }
}
