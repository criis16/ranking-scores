<?php

namespace Tests\User\Application;

use App\User\Application\AddScoreDTO;
use PHPUnit\Framework\TestCase;

final class AddScoreDTOTest extends TestCase
{
    private const string USER_ID = '550e8400-e29b-41d4-a716-446655440000';

    public function test_it_creates_absolute_dto_with_only_total_score(): void
    {
        $dto = AddScoreDTO::absolute(self::USER_ID, 150);

        self::assertSame(self::USER_ID, $dto->id);
        self::assertSame(150, $dto->totalScore);
        self::assertNull($dto->relativeScore);
        self::assertNull($dto->operation);
    }

    public function test_it_creates_relative_dto_with_only_relative_fields(): void
    {
        $dto = AddScoreDTO::relative(self::USER_ID, 10, '+');

        self::assertSame(self::USER_ID, $dto->id);
        self::assertNull($dto->totalScore);
        self::assertSame(10, $dto->relativeScore);
        self::assertSame('+', $dto->operation);
    }
}
