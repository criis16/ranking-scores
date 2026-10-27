<?php

namespace Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\UuidValueObject;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tests\Shared\Domain\ValueObject\DataProvider\UuidValueObjectDataProvider;

final class UuidValueObjectStub extends UuidValueObject
{
}

final class UuidValueObjectTest extends TestCase
{
    public function test_it_keeps_a_well_formed_uuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';

        self::assertSame($uuid, new UuidValueObjectStub($uuid)->value());
    }

    #[DataProviderExternal(UuidValueObjectDataProvider::class, 'invalidUuids')]
    public function test_it_rejects_an_invalid_uuid(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UuidValueObjectStub($value);
    }

    public function test_it_generates_a_valid_uuid(): void
    {
        $id = UuidValueObjectStub::generate();

        self::assertInstanceOf(UuidValueObjectStub::class, $id);
        self::assertTrue(Uuid::isValid($id->value()));
    }

    public function test_it_generates_distinct_uuids(): void
    {
        self::assertNotSame(
            UuidValueObjectStub::generate()->value(),
            UuidValueObjectStub::generate()->value()
        );
    }

    #[DataProviderExternal(UuidValueObjectDataProvider::class, 'validUuids')]
    public function test_it_compares_equality_by_value(string $uuid): void
    {
        $stub = new UuidValueObjectStub($uuid);

        self::assertTrue($stub->equals($stub));
        self::assertFalse($stub->equals(UuidValueObjectStub::generate()));
    }

    #[DataProviderExternal(UuidValueObjectDataProvider::class, 'validUuids')]
    public function test_it_stringifies_to_its_value(string $uuid): void
    {
        self::assertSame($uuid, (string)new UuidValueObjectStub($uuid));
    }
}
