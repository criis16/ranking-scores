<?php

namespace Tests\Shared\Infrastructure\Persistence;

use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filters;
use App\Shared\Domain\Criteria\Order;
use App\Shared\Infrastructure\Persistence\PdoCriteriaConverter;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Infrastructure\Persistence\DataProvider\PdoCriteriaConverterDataProvider;

final class PdoCriteriaConverterTest extends TestCase
{
    private const array FIELD_MAP = ['id' => 'id', 'score' => 'score'];

    #[DataProviderExternal(PdoCriteriaConverterDataProvider::class, 'operators')]
    public function test_it_builds_where_for_each_operator(string $operator, string $expectedCondition): void
    {
        $result = PdoCriteriaConverter::convert(
            self::criteriaWithFilter('score', $operator, '10'),
            self::FIELD_MAP
        );

        self::assertSame('WHERE ' . $expectedCondition, $result['where']);
        self::assertSame([':p0' => '10'], $result['params']);
    }

    public function test_it_returns_empty_where_when_there_are_no_filters(): void
    {
        $result = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::none()),
            self::FIELD_MAP
        );

        self::assertSame('', $result['where']);
        self::assertSame([], $result['params']);
    }

    public function test_it_combines_multiple_filters_with_and(): void
    {
        $criteria = new Criteria(
            Filters::fromValues([
                ['field' => 'id', 'operator' => '=', 'value' => 'a'],
                ['field' => 'score', 'operator' => '<', 'value' => '5'],
            ]),
            Order::none()
        );

        $result = PdoCriteriaConverter::convert($criteria, self::FIELD_MAP);

        self::assertSame('WHERE id = :p0 AND score < :p1', $result['where']);
        self::assertSame([':p0' => 'a', ':p1' => '5'], $result['params']);
    }

    public function test_it_builds_order_by_column_and_direction(): void
    {
        $desc = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::fromValues('score', 'desc')),
            self::FIELD_MAP
        );
        $asc = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::fromValues('score', 'asc')),
            self::FIELD_MAP
        );

        self::assertSame('ORDER BY score DESC', $desc['order']);
        self::assertSame('ORDER BY score ASC', $asc['order']);
    }

    public function test_it_returns_empty_order_when_order_is_none(): void
    {
        $result = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::none()),
            self::FIELD_MAP
        );

        self::assertSame('', $result['order']);
    }

    public function test_it_builds_limit_when_limit_is_present(): void
    {
        $result = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::none(), null, 3),
            self::FIELD_MAP
        );

        self::assertSame('LIMIT 3', $result['limit']);
    }

    public function test_it_builds_limit_and_offset_when_both_are_present(): void
    {
        $result = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::none(), 6, 3),
            self::FIELD_MAP
        );

        self::assertSame('LIMIT 3 OFFSET 6', $result['limit']);
    }

    public function test_it_returns_empty_limit_when_no_limit_and_no_offset(): void
    {
        $result = PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::none()),
            self::FIELD_MAP
        );

        self::assertSame('', $result['limit']);
    }

    public function test_it_rejects_a_field_outside_the_map(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field <id> is not searchable.');

        PdoCriteriaConverter::convert(self::criteriaWithFilter('id', '=', 'x'), ['score' => 'score']);
    }

    public function test_it_rejects_an_order_field_outside_the_map(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field <score> is not searchable.');

        PdoCriteriaConverter::convert(
            new Criteria(Filters::none(), Order::fromValues('score', 'desc')),
            ['id' => 'id']
        );
    }

    private static function criteriaWithFilter(string $field, string $operator, string $value): Criteria
    {
        return new Criteria(
            Filters::fromValues([['field' => $field, 'operator' => $operator, 'value' => $value]]),
            Order::none()
        );
    }
}
