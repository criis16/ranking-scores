<?php

namespace Tests\User\Infrastructure\Persistence;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filters;
use App\Shared\Domain\Criteria\Order;
use App\Shared\Domain\Criteria\OrderBy;
use App\User\Domain\User;
use App\User\Domain\UserId;
use App\User\Infrastructure\Persistence\PgsqlUserRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PgsqlUserRepositoryTest extends TestCase
{
    private const string USER_ID = '550e8400-e29b-41d4-a716-446655440000';

    private const string OTHER_USER_ID = '3f1d3e19-8f7e-4e0a-9d3b-2a6c5b8e1f44';

    private static bool $schemaReady = false;

    private PgsqlUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = DB::connection();

        if (!self::$schemaReady) {
            if (!$connection->getSchemaBuilder()->hasTable('app_users')) {
                (require database_path('migrations/2026_10_31_000001_create_app_users_table.php'))->up();
            }

            self::$schemaReady = true;
        }

        $this->repository = new PgsqlUserRepository($connection->getPdo());
        $connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = DB::connection();

        if ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }

        parent::tearDown();
    }

    public function test_it_saves_and_retrieves_a_user(): void
    {
        $this->repository->save($this->createUser(self::USER_ID, 150));

        $users = $this->findById(self::USER_ID);

        self::assertCount(1, $users);
        self::assertSame(self::USER_ID, $users[0]->id()->value());
        self::assertSame(150, $users[0]->score()->points()->value());
    }

    public function test_it_returns_empty_array_when_no_user_matches(): void
    {
        $this->repository->save($this->createUser(self::USER_ID, 150));

        self::assertSame([], $this->findById(self::OTHER_USER_ID));
    }

    public function test_it_updates_score_when_saving_the_same_user_again(): void
    {
        $this->repository->save($this->createUser(self::USER_ID, 50));
        $this->repository->save($this->createUser(self::USER_ID, 80));

        $users = $this->findById(self::USER_ID);

        self::assertCount(1, $users);
        self::assertSame(80, $users[0]->score()->points()->value());
    }

    public function test_it_retrieves_only_the_user_with_the_requested_id(): void
    {
        $this->repository->save($this->createUser(self::USER_ID, 50));
        $this->repository->save($this->createUser(self::OTHER_USER_ID, 20));

        $users = $this->findById(self::USER_ID);

        self::assertCount(1, $users);
        self::assertSame(self::USER_ID, $users[0]->id()->value());
    }

    public function test_it_orders_by_score_desc_and_limits(): void
    {
        $this->repository->save($this->createUser(self::USER_ID, 50));
        $this->repository->save($this->createUser(self::OTHER_USER_ID, 150));

        $users = $this->repository->searchByCriteria(new Criteria(
            Filters::none(),
            Order::createDesc(new OrderBy('score')),
            null,
            1
        ));

        self::assertCount(1, $users);
        self::assertSame(self::OTHER_USER_ID, $users[0]->id()->value());
        self::assertSame(150, $users[0]->score()->points()->value());
    }

    public function test_it_applies_offset_to_the_ordered_result(): void
    {
        foreach ([100, 200, 300, 400, 500] as $index => $score) {
            $this->repository->save($this->createUser(
                sprintf('00000000-0000-4000-8000-%012d', $index),
                $score
            ));
        }

        $users = $this->repository->searchByCriteria(new Criteria(
            Filters::none(),
            Order::createDesc(new OrderBy('score')),
            2,
            2
        ));

        self::assertCount(2, $users);
        self::assertSame(300, $users[0]->score()->points()->value());
        self::assertSame(200, $users[1]->score()->points()->value());
    }

    private function createUser(string $id, int $score): User
    {
        return new User(
            new UserId($id),
            new AbsoluteScore(ScorePoints::create($score))
        );
    }

    private function findById(string $id): array
    {
        return $this->repository->searchByCriteria(new Criteria(
            Filters::fromValues([
                ['field' => 'id', 'operator' => '=', 'value' => $id],
            ]),
            Order::none()
        ));
    }
}
