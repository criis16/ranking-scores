<?php

namespace Score\Domain\DataProvider;

use App\Score\Domain\RelativeScoreOperation;

final class RelativeScoreOperationDataProvider
{
    /**
     * @return iterable<string, array{string, RelativeScoreOperation}>
     */
    public static function validOperations(): iterable
    {
        yield 'add operation' => ['+', RelativeScoreOperation::Add];
        yield 'subtract operation' => ['-', RelativeScoreOperation::Subtract];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidOperations(): iterable
    {
        yield 'empty string' => [''];
        yield 'numeric string' => ['123'];
        yield 'special characters' => ['@#$%'];
        yield 'unknown symbol' => ['*'];
        yield 'synonym textual operation' => ['plus'];
        yield 'uppercase textual operation' => ['ADD'];
    }
}
