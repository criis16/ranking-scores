<?php

namespace Tests\Shared\Domain\Criteria;

use App\Shared\Domain\Criteria\Order;
use App\Shared\Domain\Criteria\OrderBy;
use App\Shared\Domain\Criteria\OrderType;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Domain\Criteria\DataProvider\OrderDataProvider;

final class OrderTest extends TestCase
{
    public function test_none_order_has_empty_order_by_and_none_type(): void
    {
        $order = Order::none();

        self::assertSame('', $order->orderBy()->value());
        self::assertSame(OrderType::NONE, $order->orderType());
        self::assertTrue($order->isNone());
    }

    public function test_it_creates_descending_order(): void
    {
        $order = Order::createDesc(new OrderBy('score'));

        self::assertSame('score', $order->orderBy()->value());
        self::assertSame(OrderType::DESC, $order->orderType());
        self::assertFalse($order->isNone());
    }

    #[DataProviderExternal(OrderDataProvider::class, 'nullCombinations')]
    public function test_it_is_none_when_any_of_the_values_is_null(?string $orderBy, ?string $order): void
    {
        self::assertTrue(Order::fromValues($orderBy, $order)->isNone());
    }

    public function test_it_builds_order_from_values(): void
    {
        $order = Order::fromValues('score', 'asc');

        self::assertSame('score', $order->orderBy()->value());
        self::assertSame(OrderType::ASC, $order->orderType());
        self::assertFalse($order->isNone());
    }

    public function test_it_serializes_field_and_type(): void
    {
        self::assertSame('score.asc', Order::fromValues('score', 'asc')->serialize());
        self::assertSame('.none', Order::none()->serialize());
    }

    public function test_it_rejects_an_unknown_order_type(): void
    {
        $this->expectException(\ValueError::class);

        Order::fromValues('score', 'sideways');
    }
}
