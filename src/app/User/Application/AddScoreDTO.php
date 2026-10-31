<?php

namespace App\User\Application;

final readonly class AddScoreDTO
{
    private function __construct(
        public string  $id,
        public ?int    $totalScore,
        public ?int    $relativeScore,
        public ?string $operation,
    )
    {
    }

    public static function absolute(string $id, int $totalScore): self
    {
        return new self($id, $totalScore, null, null);
    }

    public static function relative(string $id, int $score, string $operation): self
    {
        return new self($id, null, $score, $operation);
    }
}
