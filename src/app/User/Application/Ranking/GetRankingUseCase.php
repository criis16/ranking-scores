<?php

namespace App\User\Application\Ranking;

use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filters;
use App\Shared\Domain\Criteria\Order;
use App\Shared\Domain\Criteria\OrderBy;
use App\User\Domain\UserRepository;

final readonly class GetRankingUseCase
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function __invoke(RankingDTO $dto): array
    {
        if (!is_null($dto->position) && $dto->position < 1) {
            return [];
        }

        $offset = !is_null($dto->position) ? max(0, $dto->position - 1 - $dto->range) : null;
        $limit = !is_null($dto->position)
            ? ($dto->position - 1 + $dto->range) - $offset + 1
            : $dto->top;

        return $this->repository->searchByCriteria(new Criteria(
            Filters::none(),
            Order::createDesc(new OrderBy('score')),
            $offset,
            $limit
        ));
    }
}
