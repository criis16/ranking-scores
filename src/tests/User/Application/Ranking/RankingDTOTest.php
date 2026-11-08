<?php

namespace Tests\User\Application\Ranking;

use App\User\Application\Ranking\RankingDTO;
use PHPUnit\Framework\TestCase;

final class RankingDTOTest extends TestCase
{
    public function test_it_creates_top_dto_with_only_top_field(): void
    {
        $dto = RankingDTO::top(5);

        self::assertSame(5, $dto->top);
        self::assertNull($dto->position);
        self::assertNull($dto->range);
    }

    public function test_it_creates_at_dto_with_only_position_fields(): void
    {
        $dto = RankingDTO::at(3, 2);

        self::assertNull($dto->top);
        self::assertSame(3, $dto->position);
        self::assertSame(2, $dto->range);
    }
}
