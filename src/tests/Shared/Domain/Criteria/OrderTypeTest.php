<?php

namespace Tests\Shared\Domain\Criteria;

use App\Shared\Domain\Criteria\OrderType;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Domain\Criteria\DataProvider\OrderTypeDataProvider;

final class OrderTypeTest extends TestCase
{
    #[DataProviderExternal(OrderTypeDataProvider::class, 'orderTypes')]
    public function test_it_knows_if_it_is_none(OrderType $type, bool $expected): void
    {
        self::assertSame($expected, $type->isNone());
    }

    public function test_it_has_explicit_backing_values(): void
    {
        self::assertSame('asc', OrderType::ASC->value);
        self::assertSame('desc', OrderType::DESC->value);
        self::assertSame('none', OrderType::NONE->value);
    }

    public function test_it_resolves_from_backing_values(): void
    {
        self::assertSame(OrderType::ASC, OrderType::from('asc'));
        self::assertSame(OrderType::DESC, OrderType::from('desc'));
        self::assertSame(OrderType::NONE, OrderType::from('none'));
    }
}
