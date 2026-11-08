<?php

namespace Tests\User\Application\Ranking\DataProvider;

use App\User\Application\Ranking\RankingDTO;

final class GetRankingUseCaseDataProvider
{
    /**
     * @return iterable<string, array{array<int>, RankingDTO, array<int>}>
     *                                                                     [scores sembrados en orden de inserción, dto del ranking, scores esperados en orden de ranking]
     */
    public static function rankingScenarios(): iterable
    {
        yield 'top with fewer users than requested' => [[100, 90, 80], RankingDTO::top(2), [100, 90]];
        yield 'top with more users than exist' => [[100, 90], RankingDTO::top(5), [100, 90]];
        yield 'top ranks by score regardless of insertion order' => [[80, 100, 90], RankingDTO::top(2), [100, 90]];
        yield 'at returns the users around a middle position' => [[100, 90, 80, 70, 60], RankingDTO::at(3, 1), [90, 80, 70]];
        yield 'at clamps the range at the beginning' => [[100, 90, 80, 70, 60], RankingDTO::at(1, 2), [100, 90, 80]];
        yield 'at clamps the range at the end' => [[100, 90, 80, 70, 60], RankingDTO::at(5, 2), [80, 70, 60]];
        yield 'at with a range wider than the ranking returns everyone' => [[100, 90, 80], RankingDTO::at(2, 10), [100, 90, 80]];
    }
}
