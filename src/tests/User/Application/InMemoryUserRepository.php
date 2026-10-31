<?php

namespace Tests\User\Application;

use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filter;
use App\Shared\Domain\Criteria\FilterOperator;
use App\User\Domain\User;
use App\User\Domain\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    /** @var User[] */
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[$user->id()->value()] = $user;
    }

    public function searchByCriteria(Criteria $criteria): array
    {
        $users = $this->findAll();

        foreach ($criteria->plainFilters() as $filter) {
            $users = array_values(
                array_filter($users, fn(User $user): bool => $this->matches($user, $filter))
            );
        }

        return $users;
    }

    /** @return User[] */
    public function findAll(): array
    {
        return array_values($this->users);
    }

    private function matches(User $user, Filter $filter): bool
    {
        return match ($filter->field()->value()) {
            'id' => match ($filter->operator()) {
                FilterOperator::EQUAL => $user->id()->value() === $filter->value()->value(),
                FilterOperator::NOT_EQUAL => $user->id()->value() !== $filter->value()->value(),
            },
            default => throw new \LogicException(
                sprintf('<%s> is not searchable.', $filter->field()->value())
            ),
        };
    }
}
