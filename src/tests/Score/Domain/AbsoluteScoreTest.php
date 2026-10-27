<?php

namespace Tests\Score\Domain;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\Score\Domain\ScoreType;
use PHPUnit\Framework\TestCase;

final class AbsoluteScoreTest extends TestCase
{
    public function test_it_initializes_with_zero_points(): void
    {
        self::assertSame(0, AbsoluteScore::initialize()->points()->value());
    }

    public function test_it_reports_the_absolute_type(): void
    {
        $score = new AbsoluteScore(ScorePoints::create(10));

        self::assertSame(ScoreType::Absolute, $score->type());
    }

    public function test_it_applies_by_adding_the_other_score_points_immutably(): void
    {
        $score = new AbsoluteScore(ScorePoints::create(10));
        $other = new AbsoluteScore(ScorePoints::create(5));

        $result = $score->applyTo($other);

        self::assertSame(15, $result->points()->value());
        self::assertSame(10, $score->points()->value());
        self::assertSame(5, $other->points()->value());
    }

    public function test_it_applies_to_an_initialized_score(): void
    {
        $score = new AbsoluteScore(ScorePoints::create(7));

        $result = $score->applyTo(AbsoluteScore::initialize());

        self::assertSame(7, $result->points()->value());
    }
}
