<?php

namespace App\User\Domain;

use App\Shared\Domain\Criteria\Criteria;

interface UserRepository
{
    public function save(User $user): void;

    /** @return User[] */
    public function searchByCriteria(Criteria $criteria): array;
}
