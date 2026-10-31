<?php

namespace Tests\Shared\Domain\Criteria;

use App\Shared\Domain\Criteria\FilterOperator;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Domain\Criteria\DataProvider\FilterOperatorDataProvider;

final class FilterOperatorTest extends TestCase
{
    #[DataProviderExternal(FilterOperatorDataProvider::class, 'operators')]
    public function test_it_knows_if_it_is_containing(FilterOperator $operator, bool $expected): void
    {
        self::assertSame($expected, $operator->isContaining());
    }

    public function test_it_has_explicit_backing_values(): void
    {
        self::assertSame('=', FilterOperator::EQUAL->value);
        self::assertSame('!=', FilterOperator::NOT_EQUAL->value);
        self::assertSame('>', FilterOperator::GT->value);
        self::assertSame('<', FilterOperator::LT->value);
        self::assertSame('CONTAINS', FilterOperator::CONTAINS->value);
        self::assertSame('NOT_CONTAINS', FilterOperator::NOT_CONTAINS->value);
    }

    public function test_it_resolves_from_backing_values(): void
    {
        self::assertSame(FilterOperator::EQUAL, FilterOperator::from('='));
        self::assertSame(FilterOperator::NOT_EQUAL, FilterOperator::from('!='));
        self::assertSame(FilterOperator::GT, FilterOperator::from('>'));
        self::assertSame(FilterOperator::LT, FilterOperator::from('<'));
        self::assertSame(FilterOperator::CONTAINS, FilterOperator::from('CONTAINS'));
        self::assertSame(FilterOperator::NOT_CONTAINS, FilterOperator::from('NOT_CONTAINS'));
    }
}
