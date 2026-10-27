<?php

namespace Tests\User\Domain;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\User\Domain\User;
use App\User\Domain\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_it_is_created_with_id_and_initialized_score(): void
    {
        $id = UserId::generate();
        $user = User::create($id);

        self::assertSame($id, $user->id());
        self::assertInstanceOf(AbsoluteScore::class, $user->score());
        self::assertSame(0, $user->score()->points()->value());
    }

    public function test_it_is_created_with_constructor(): void
    {
        $id = UserId::generate();
        $score = AbsoluteScore::initialize();
        $user = new User($id, $score);

        self::assertSame($id, $user->id());
        self::assertSame($score, $user->score());
    }

    public function test_it_applies_score(): void
    {
        $user = User::create(UserId::generate());
        $additionalScore = new AbsoluteScore(ScorePoints::create(10));
        $user->apply($additionalScore);

        self::assertSame(10, $user->score()->points()->value());
    }

    public function test_it_applies_multiple_scores(): void
    {
        $user = User::create(UserId::generate());
        $user->apply(new AbsoluteScore(ScorePoints::create(5)));
        $user->apply(new AbsoluteScore(ScorePoints::create(3)));

        self::assertSame(8, $user->score()->points()->value());
    }
}
