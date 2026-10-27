<?php

namespace Tests\Score\Domain;

use App\Score\Domain\ScorePoints;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Score\Domain\DataProvider\ScorePointsDataProvider;

final class ScorePointsTest extends TestCase
{
    public function test_it_initializes_with_zero_points(): void
    {
        self::assertSame(0, ScorePoints::initialize()->value());
    }

    #[DataProviderExternal(ScorePointsDataProvider::class, 'validPoints')]
    public function test_it_creates_points_from_non_negative_values(int $value): void
    {
        $points = ScorePoints::create($value);

        self::assertSame($value, $points->value());
        self::assertFalse($points->isNegativeValue());
    }

    #[DataProviderExternal(ScorePointsDataProvider::class, 'negativePoints')]
    public function test_it_clamps_negative_points_to_zero(int $value): void
    {
        $points = ScorePoints::create($value);

        self::assertSame(0, $points->value());
        self::assertFalse($points->isNegativeValue());
    }

    public function test_it_adds_points_immutably(): void
    {
        $points = ScorePoints::create(5);

        $result = $points->add(ScorePoints::create(10));

        self::assertSame(15, $result->value());
        self::assertSame(5, $points->value());
    }

    public function test_it_subtracts_points_immutably(): void
    {
        $points = ScorePoints::create(15);

        $result = $points->subtract(ScorePoints::create(10));

        self::assertSame(5, $result->value());
        self::assertSame(15, $points->value());
    }

    public function test_it_clamps_subtraction_below_zero_to_zero(): void
    {
        $points = ScorePoints::create(5);

        $result = $points->subtract(ScorePoints::create(10));

        self::assertSame(0, $result->value());
        self::assertSame(5, $points->value());
    }
}
