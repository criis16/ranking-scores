<?php

namespace Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\IntValueObject;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Domain\ValueObject\DataProvider\IntValueObjectDataProvider;

final class IntValueObjectStub extends IntValueObject
{
}

final class IntValueObjectTest extends TestCase
{
    #[DataProviderExternal(IntValueObjectDataProvider::class, 'anyIntegers')]
    public function test_it_stores_any_integer_value(int $value): void
    {
        self::assertSame($value, new IntValueObjectStub($value)->value());
    }

    #[DataProviderExternal(IntValueObjectDataProvider::class, 'nonNegativeValues')]
    public function test_it_reports_no_negative_value_for_non_negative_values(int $value): void
    {
        $stub = new IntValueObjectStub($value);

        self::assertFalse($stub->isNegativeValue());
    }

    #[DataProviderExternal(IntValueObjectDataProvider::class, 'negativeValues')]
    public function test_it_reports_negative_value_for_negative_values(int $value): void
    {
        $stub = new IntValueObjectStub($value);

        self::assertTrue($stub->isNegativeValue());
    }
}
