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
    public function test_it_resolves_invalid_score_types(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ScoreType::fromValue($value);
    }
}
