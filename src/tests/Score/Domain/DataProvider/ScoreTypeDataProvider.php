<?php

namespace Tests\Score\Domain\DataProvider;

use App\Score\Domain\ScoreType;

final class ScoreTypeDataProvider
{
    /**
     * @return iterable<string, array{string, ScoreType}>
     */
    public static function validTypes(): iterable
    {
        yield 'absolute score' => ['absolute_score', ScoreType::Absolute];
        yield 'relative score' => ['relative_score', ScoreType::Relative];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidTypes(): iterable
    {
        yield 'empty string' => [''];
        yield 'invalid type' => ['invalid_score'];
        yield 'numeric string' => ['123'];
        yield 'special characters' => ['@#$%'];
    }
}
