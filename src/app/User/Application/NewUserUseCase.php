<?php

namespace App\User\Application;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\RelativeScore;
use App\Score\Domain\RelativeScoreOperation;
use App\Score\Domain\Score;
use App\Score\Domain\ScorePoints;
use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filters;
use App\Shared\Domain\Criteria\Order;
use App\User\Domain\User;
use App\User\Domain\UserId;
use App\User\Domain\UserRepository;

final readonly class NewUserUseCase
{
    public function __construct(private UserRepository $users)
    {
    }

    public function __invoke(AddScoreDTO $dto): void
    {
        $userId = new UserId($dto->id);
        $user = $this->findUser($userId) ?? User::create($userId);
        $user->apply($this->buildScore($dto));
        $this->users->save($user);
    }

    private function findUser(UserId $userId): ?User
    {
        $users = $this->users->searchByCriteria(new Criteria(
            Filters::fromValues([
                ['field' => 'id', 'operator' => '=', 'value' => $userId->value()],
            ]),
            Order::none()
        ));

        return $users[0] ?? null;
    }

    private function buildScore(AddScoreDTO $dto): Score
    {
        if ($dto->totalScore !== null) {
            return new AbsoluteScore(ScorePoints::create($dto->totalScore));
        }

        return new RelativeScore(
            ScorePoints::create($dto->relativeScore),
            RelativeScoreOperation::fromValue($dto->operation)
        );
    }
}
