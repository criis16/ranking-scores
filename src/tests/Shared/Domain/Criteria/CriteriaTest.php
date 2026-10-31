<?php

namespace Tests\Shared\Domain\Criteria;

use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filters;
use App\Shared\Domain\Criteria\Order;
use App\Shared\Domain\Criteria\OrderBy;
use App\Shared\Domain\Criteria\OrderType;
use PHPUnit\Framework\TestCase;

final class CriteriaTest extends TestCase
{
    public function test_it_has_filters_when_constructed_with_filters(): void
    {
        $criteria = new Criteria(self::buildFilters(), Order::none());

        self::assertTrue($criteria->hasFilters());
        self::assertCount(1, $criteria->plainFilters());
    }

    public function test_it_has_no_filters_when_constructed_with_none_filters(): void
    {
        $criteria = new Criteria(Filters::none(), Order::none());

        self::assertFalse($criteria->hasFilters());
        self::assertSame([], $criteria->plainFilters());
    }

    public function test_it_has_order_when_constructed_with_order(): void
    {
        $criteria = new Criteria(Filters::none(), Order::createDesc(new OrderBy('score')));

        self::assertTrue($criteria->hasOrder());
        self::assertSame(OrderType::DESC, $criteria->order()->orderType());
    }

    public function test_it_has_no_order_when_constructed_with_none_order(): void
    {
        $criteria = new Criteria(Filters::none(), Order::none());

        self::assertFalse($criteria->hasOrder());
        self::assertSame(OrderType::NONE, $criteria->order()->orderType());
    }

    public function test_it_defaults_to_no_offset_or_limit(): void
    {
        $criteria = new Criteria(Filters::none(), Order::none());

        self::assertNull($criteria->offset());
        self::assertNull($criteria->limit());
    }

    public function test_it_accepts_offset_and_limit(): void
    {
        $criteria = new Criteria(Filters::none(), Order::none(), 10, 5);

        self::assertSame(10, $criteria->offset());
        self::assertSame(5, $criteria->limit());
    }

    public function test_it_exposes_filters_instance_and_plain_array(): void
    {
        $filters = self::buildFilters();
        $criteria = new Criteria($filters, Order::none());

        self::assertSame($filters, $criteria->filters());
        self::assertSame($filters->filters(), $criteria->plainFilters());
    }

    private static function buildFilters(): Filters
    {
        return Filters::fromValues([
            ['field' => 'score', 'operator' => '=', 'value' => '10'],
        ]);
    }
}
