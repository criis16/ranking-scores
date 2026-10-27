<?php

namespace Tests\Score\Domain;

use App\Score\Domain\ScoreType;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Score\Domain\DataProvider\ScoreTypeDataProvider;

final class ScoreTypeTest extends TestCase
{
    #[DataProviderExternal(ScoreTypeDataProvider::class, 'validTypes')]
    public function test_it_resolves_valid_score_types(string $value, ScoreType $expected): void
    {
        self::assertSame($expected, ScoreType::fromValue($value));
    }

    #[DataProviderExternal(ScoreTypeDataProvider::class, 'invalidTypes')]
    public function test_it_rejects_invalid_score_types(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ScoreType::fromValue($value);
    }

    public function test_it_has_explicit_backing_values(): void
    {
        self::assertSame('absolute_score', ScoreType::Absolute->value);
        self::assertSame('relative_score', ScoreType::Relative->value);
    }

    public function test_it_rejects_wrong_case_score_types(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ScoreType::fromValue('ABSOLUTE_SCORE');
    }

    public function test_it_rejects_score_types_with_surrounding_spaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ScoreType::fromValue(' absolute_score ');
    }

    public function test_it_rejects_partial_score_types(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ScoreType::fromValue('absolute');
    }
}
