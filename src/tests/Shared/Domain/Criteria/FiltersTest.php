<?php

namespace Tests\Shared\Domain\Criteria;

use App\Shared\Domain\Criteria\Filter;
use App\Shared\Domain\Criteria\Filters;
use PHPUnit\Framework\TestCase;

final class FiltersTest extends TestCase
{
    public function test_it_builds_filters_from_values(): void
    {
        $filters = Filters::fromValues([
            ['field' => 'score', 'operator' => '=', 'value' => '10'],
            ['field' => 'score', 'operator' => '>', 'value' => '1'],
        ]);

        self::assertSame(2, $filters->count());
        self::assertFalse($filters->isEmpty());
        self::assertContainsOnlyInstancesOf(Filter::class, $filters->filters());
    }

    public function test_it_creates_empty_filters(): void
    {
        $filters = Filters::none();

        self::assertSame(0, $filters->count());
        self::assertTrue($filters->isEmpty());
        self::assertSame([], $filters->filters());
    }

    public function test_add_returns_a_new_instance_without_mutating_the_original(): void
    {
        $original = Filters::none();
        $filter = Filter::fromValues(['field' => 'score', 'operator' => '=', 'value' => '10']);

        $added = $original->add($filter);

        self::assertNotSame($original, $added);
        self::assertTrue($original->isEmpty());
        self::assertSame(1, $added->count());
        self::assertSame([$filter], $added->filters());
    }

    public function test_it_iterates_over_filters(): void
    {
        $filters = Filters::fromValues([
            ['field' => 'score', 'operator' => '=', 'value' => '10'],
        ]);

        $iterated = iterator_to_array($filters);

        self::assertCount(1, $iterated);
        self::assertInstanceOf(Filter::class, reset($iterated));
    }
}
