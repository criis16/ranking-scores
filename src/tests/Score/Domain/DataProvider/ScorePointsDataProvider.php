<?php

namespace Tests\Score\Domain\DataProvider;

final class ScorePointsDataProvider
{
    /**
     * @return iterable<string, array{int}>
     */
    public static function validPoints(): iterable
    {
        yield 'zero' => [0];
        yield 'one point' => [1];
        yield 'large positive' => [999999];
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function negativePoints(): iterable
    {
        yield 'minus one' => [-1];
        yield 'large negative' => [-999999];
    }
}
