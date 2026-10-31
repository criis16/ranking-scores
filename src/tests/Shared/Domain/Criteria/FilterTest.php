<?php

namespace Tests\Shared\Domain\Criteria;

use App\Shared\Domain\Criteria\Filter;
use App\Shared\Domain\Criteria\FilterField;
use App\Shared\Domain\Criteria\FilterOperator;
use App\Shared\Domain\Criteria\FilterValue;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    public function test_it_builds_from_values(): void
    {
        $filter = Filter::fromValues([
            'field' => 'score',
            'operator' => '=',
            'value' => '10',
        ]);

        self::assertSame('score', $filter->field()->value());
        self::assertSame(FilterOperator::EQUAL, $filter->operator());
        self::assertSame('10', $filter->value()->value());
    }

    public function test_it_holds_its_parts(): void
    {
        $filter = new Filter(
            new FilterField('score'),
            FilterOperator::GT,
            new FilterValue('2')
        );

        self::assertSame('score', $filter->field()->value());
        self::assertSame(FilterOperator::GT, $filter->operator());
        self::assertSame('2', $filter->value()->value());
    }

    public function test_it_serializes_field_operator_and_value(): void
    {
        $filter = Filter::fromValues([
            'field' => 'score',
            'operator' => '=',
            'value' => '10',
        ]);

        self::assertSame('score.=.10', $filter->serialize());
    }

    public function test_it_rejects_an_unknown_operator(): void
    {
        $this->expectException(\ValueError::class);

        Filter::fromValues([
            'field' => 'score',
            'operator' => '=??',
            'value' => '10',
        ]);
    }
}
