<?php

namespace Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\StringValueObject;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Shared\Domain\ValueObject\DataProvider\StringValueObjectDataProvider;

final class StringValueObjectStub extends StringValueObject
{
}

final class StringValueObjectTest extends TestCase
{
    #[DataProviderExternal(StringValueObjectDataProvider::class, 'anyStrings')]
    public function test_it_stores_any_string_value(string $value): void
    {
        self::assertSame($value, new StringValueObjectStub($value)->value());
    }
}
