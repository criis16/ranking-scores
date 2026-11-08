<?php

namespace Tests\User\Application\Ranking;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\User\Application\Ranking\GetRankingUseCase;
use App\User\Application\Ranking\RankingDTO;
use App\User\Domain\User;
use App\User\Domain\UserId;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\User\Application\InMemoryUserRepository;
use Tests\User\Application\Ranking\DataProvider\GetRankingUseCaseDataProvider;

final class GetRankingUseCaseTest extends TestCase
{
    private InMemoryUserRepository $users;

    private GetRankingUseCase $useCase;

    protected function setUp(): void
    {
        $this->users = new InMemoryUserRepository;
        $this->useCase = new GetRankingUseCase($this->users);
    }

    #[DataProviderExternal(GetRankingUseCaseDataProvider::class, 'rankingScenarios')]
    public function test_it_returns_the_users_in_the_expected_ranking_order(
        array $scores,
        RankingDTO $dto,
        array $expectedScores,
    ): void {
        $this->saveUsers($scores);

        $saved = $this->useCase->__invoke($dto);

        self::assertSame($expectedScores, $this->scoresOf($saved));
    }

    public function test_it_returns_no_users_when_there_are_no_users(): void
    {
        $saved = $this->useCase->__invoke(RankingDTO::top(5));

        self::assertSame([], $saved);
    }

    public function test_it_returns_no_users_when_top_is_zero(): void
    {
        $this->saveUsers([100, 90]);

        $saved = $this->useCase->__invoke(RankingDTO::top(0));

        self::assertSame([], $saved);
    }

    public function test_it_returns_no_users_when_the_position_is_zero(): void
    {
        $this->saveUsers([100, 90]);

        $saved = $this->useCase->__invoke(RankingDTO::at(0, 2));

        self::assertSame([], $saved);
    }

    public function test_it_returns_no_users_when_the_position_is_beyond_the_last_user(): void
    {
        $this->saveUsers([100, 90, 80]);

        $saved = $this->useCase->__invoke(RankingDTO::at(99, 1));

        self::assertSame([], $saved);
    }

    private function saveUsers(array $scores): void
    {
        foreach ($scores as $index => $score) {
            $this->users->save(new User(
                new UserId(sprintf('00000000-0000-4000-8000-%012d', $index)),
                new AbsoluteScore(ScorePoints::create($score))
            ));
        }
    }

    /**
     * @param  User[]  $users
     * @return int[]
     */
    private function scoresOf(array $users): array
    {
        return array_map(fn (User $user): int => $user->score()->points()->value(), $users);
    }
}
