<?php

namespace Tests\Score\Domain;

use App\Score\Domain\Score;
use App\Score\Domain\ScorePoints;
use App\Score\Domain\ScoreType;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Score\Domain\DataProvider\ScorePointsDataProvider;

final class ScoreStub extends Score
{
    public function type(): ScoreType
    {
        return ScoreType::Absolute;
    }

    public function applyTo(Score $score): Score
    {
        return $score;
    }
}

final class ScoreTest extends TestCase
{
    public function test_it_exposes_the_points_it_was_created_with(): void
    {
        $points = ScorePoints::create(5);

        $score = new ScoreStub($points);

        self::assertSame($points, $score->points());
    }

    #[DataProviderExternal(ScorePointsDataProvider::class, 'validPoints')]
    public function test_it_keeps_any_points_value(int $value): void
    {
        $score = new ScoreStub(ScorePoints::create($value));

        self::assertSame($value, $score->points()->value());
    }
}
