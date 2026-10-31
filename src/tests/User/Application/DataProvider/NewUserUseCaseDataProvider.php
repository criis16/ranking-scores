<?php

namespace Tests\User\Application\DataProvider;

final class NewUserUseCaseDataProvider
{
    /**
     * @return iterable<string, array{int|null, int|null, int|null, string|null, int}>
     */
    public static function scoreScenarios(): iterable
    {
        yield 'new user with absolute score' => [null, 150, null, null, 150];
        yield 'existing user adds absolute score to current score' => [100, 50, null, null, 150];
        yield 'new user with relative addition' => [null, null, 10, '+', 10];
        yield 'existing user with relative addition' => [100, null, 10, '+', 110];
        yield 'existing user with relative subtraction' => [100, null, 30, '-', 70];
        yield 'relative subtraction below zero clamps to zero' => [10, null, 30, '-', 0];
        yield 'negative absolute score clamps to zero' => [null, -5, null, null, 0];
    }
}
