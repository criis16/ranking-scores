<?php

namespace Tests\Score\Domain;

use App\Score\Domain\RelativeScoreOperation;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Score\Domain\DataProvider\RelativeScoreOperationDataProvider;

final class RelativeScoreOperationTest extends TestCase
{
    #[DataProviderExternal(RelativeScoreOperationDataProvider::class, 'validOperations')]
    public function test_it_resolves_valid_score_operations(string $value, RelativeScoreOperation $expected): void
    {
        self::assertSame($expected, RelativeScoreOperation::fromValue($value));
    }

    #[DataProviderExternal(RelativeScoreOperationDataProvider::class, 'invalidOperations')]
    public function test_it_rejects_invalid_score_operations(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RelativeScoreOperation::fromValue($value);
    }

    public function test_it_has_explicit_backing_values(): void
    {
        self::assertSame('+', RelativeScoreOperation::Add->value);
        self::assertSame('-', RelativeScoreOperation::Subtract->value);
    }

    public function test_it_rejects_textual_operations(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RelativeScoreOperation::fromValue('add');
    }

    public function test_it_rejects_whitespace_padded_operations(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RelativeScoreOperation::fromValue(' + ');
    }

    public function test_it_rejects_compound_operators(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RelativeScoreOperation::fromValue('++');
    }
}
