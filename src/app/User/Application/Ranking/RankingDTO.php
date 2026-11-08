<?php

namespace App\User\Application\Ranking;

final readonly class RankingDTO
{
    private function __construct(
        public ?int $top,
        public ?int $position,
        public ?int $range,
    )
    {
    }

    public static function top(int $top): self
    {
        return new self($top, null, null);
    }

    public static function at(int $position, int $range): self
    {
        return new self(null, $position, $range);
    }
}
