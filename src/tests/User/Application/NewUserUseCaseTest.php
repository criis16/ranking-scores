<?php

namespace Tests\User\Application;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Domain\Criteria\Filters;
use App\Shared\Domain\Criteria\Order;
use App\User\Application\AddScoreDTO;
use App\User\Application\NewUserUseCase;
use App\User\Domain\User;
use App\User\Domain\UserId;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\User\Application\DataProvider\NewUserUseCaseDataProvider;

final class NewUserUseCaseTest extends TestCase
{
    private const string USER_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_USER_ID = '3f1d3e19-8f7e-4e0a-9d3b-2a6c5b8e1f44';

    private InMemoryUserRepository $users;
    private NewUserUseCase $useCase;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository();
        $this->useCase = new NewUserUseCase($this->users);
    }

    #[DataProviderExternal(NewUserUseCaseDataProvider::class, 'scoreScenarios')]
    public function test_it_saves_the_user_with_the_expected_score(
        ?int    $existingScore,
        ?int    $totalScore,
        ?int    $relativeScore,
        ?string $operation,
        int     $expectedScore,
    ): void
    {
        if ($existingScore !== null) {
            $this->saveUser(self::USER_ID, $existingScore);
        }

        $this->useCase->__invoke($this->buildDTO($totalScore, $relativeScore, (string)$operation));

        $saved = $this->users->searchByCriteria($this->criteriaFor(self::USER_ID));

        self::assertCount(1, $saved);
        self::assertSame($expectedScore, $saved[0]->score()->points()->value());
    }

    public function test_it_accumulates_score_on_the_same_user_when_invoked_twice(): void
    {
        $this->useCase->__invoke(AddScoreDTO::absolute(self::USER_ID, 100));
        $this->useCase->__invoke(AddScoreDTO::relative(self::USER_ID, 10, '+'));

        $saved = $this->users->searchByCriteria($this->criteriaFor(self::USER_ID));

        self::assertCount(1, $saved);
        self::assertSame(110, $saved[0]->score()->points()->value());
    }

    public function test_it_updates_only_the_targeted_user(): void
    {
        $this->saveUser(self::USER_ID, 50);
        $this->saveUser(self::OTHER_USER_ID, 20);

        $this->useCase->__invoke(AddScoreDTO::relative(self::USER_ID, 10, '+'));

        self::assertSame(60, $this->scoreOf(self::USER_ID));
        self::assertSame(20, $this->scoreOf(self::OTHER_USER_ID));
        self::assertCount(2, $this->users->findAll());
    }

    public function test_it_throws_and_does_not_save_when_id_is_not_a_valid_uuid(): void
    {
        try {
            $this->useCase->__invoke(AddScoreDTO::absolute('not-a-uuid', 100));
            self::fail('Expected InvalidArgumentException to be thrown.');
        } catch (\InvalidArgumentException) {
        }

        self::assertSame([], $this->users->findAll());
    }

    public function test_it_throws_and_does_not_save_when_operation_is_not_allowed(): void
    {
        try {
            $this->useCase->__invoke(AddScoreDTO::relative(self::USER_ID, 10, '*'));
            self::fail('Expected InvalidArgumentException to be thrown.');
        } catch (\InvalidArgumentException) {
        }

        self::assertSame([], $this->users->findAll());
    }

    private function buildDTO(?int $totalScore, ?int $relativeScore, string $operation): AddScoreDTO
    {
        if ($totalScore !== null) {
            return AddScoreDTO::absolute(self::USER_ID, $totalScore);
        }

        return AddScoreDTO::relative(self::USER_ID, (int)$relativeScore, $operation);
    }

    private function saveUser(string $id, int $score): void
    {
        $this->users->save(new User(
            new UserId($id),
            new AbsoluteScore(ScorePoints::create($score))
        ));
    }

    private function scoreOf(string $id): int
    {
        $users = $this->users->searchByCriteria($this->criteriaFor($id));

        return $users[0]->score()->points()->value();
    }

    private function criteriaFor(string $id): Criteria
    {
        return new Criteria(
            Filters::fromValues([
                ['field' => 'id', 'operator' => '=', 'value' => $id],
            ]),
            Order::none()
        );
    }
}
