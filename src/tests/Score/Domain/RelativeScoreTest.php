<?php

namespace Score\Domain;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\RelativeScore;
use App\Score\Domain\RelativeScoreOperation;
use App\Score\Domain\ScorePoints;
use App\Score\Domain\ScoreType;
use PHPUnit\Framework\TestCase;

final class RelativeScoreTest extends TestCase
{
    public function test_it_reports_the_relative_type(): void
    {
        $score = new RelativeScore(ScorePoints::create(5), RelativeScoreOperation::Add);

        self::assertSame(ScoreType::Relative, $score->type());
    }

    public function test_it_exposes_its_operation(): void
    {
        $add = new RelativeScore(ScorePoints::create(5), RelativeScoreOperation::Add);
        $subtract = new RelativeScore(ScorePoints::create(5), RelativeScoreOperation::Subtract);

        self::assertSame(RelativeScoreOperation::Add, $add->operation());
        self::assertSame(RelativeScoreOperation::Subtract, $subtract->operation());
    }

    public function test_it_applies_the_add_operation_immutably(): void
    {
        $score = new RelativeScore(ScorePoints::create(5), RelativeScoreOperation::Add);
        $base = new AbsoluteScore(ScorePoints::create(10));

        $result = $score->applyTo($base);

        self::assertSame(15, $result->points()->value());
        self::assertSame(5, $score->points()->value());
        self::assertSame(10, $base->points()->value());
    }

    public function test_it_applies_the_subtract_operation_immutably(): void
    {
        $score = new RelativeScore(ScorePoints::create(3), RelativeScoreOperation::Subtract);
        $base = new AbsoluteScore(ScorePoints::create(10));

        $result = $score->applyTo($base);

        self::assertSame(7, $result->points()->value());
        self::assertSame(3, $score->points()->value());
        self::assertSame(10, $base->points()->value());
    }

    public function test_it_keeps_its_operation_when_applying(): void
    {
        $score = new RelativeScore(ScorePoints::create(3), RelativeScoreOperation::Subtract);

        $result = $score->applyTo(new AbsoluteScore(ScorePoints::create(10)));

        self::assertSame(RelativeScoreOperation::Subtract, $result->operation());
    }

    public function test_it_clamps_subtraction_below_zero_to_zero(): void
    {
        $score = new RelativeScore(ScorePoints::create(15), RelativeScoreOperation::Subtract);
        $base = new AbsoluteScore(ScorePoints::create(10));

        $result = $score->applyTo($base);

        self::assertSame(0, $result->points()->value());
    }
}
