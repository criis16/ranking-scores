<?php

namespace App\User\Domain;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\Score;

final class User
{
    public function __construct(
        private readonly UserId $id,
        private Score           $score
    )
    {
    }

    public static function create(UserId $id): self
    {
        return new self($id, AbsoluteScore::initialize());
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function score(): Score
    {
        return $this->score;
    }

    public function apply(Score $score): void
    {
        $this->score = $score->applyTo($this->score);
    }
}
