<?php

namespace Tests\User\Controllers;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\User\Domain\User;
use App\User\Domain\UserId;
use App\User\Domain\UserRepository;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\User\Application\InMemoryUserRepository;

final class RankingControllerTest extends TestCase
{
    private const string SUCCESS_MESSAGE = 'Users found successfully';

    private const string NOT_FOUND_MESSAGE = 'No users found';

    private const string REQUIRED_TYPE_MESSAGE = 'The type field is required.';

    private const string INVALID_TYPE_MESSAGE = 'The type field format is invalid.';

    private const string FIRST_ID = '11111111-1111-4111-8111-111111111111';

    private const string SECOND_ID = '22222222-2222-4222-8222-222222222222';

    private const string THIRD_ID = '33333333-3333-4333-8333-333333333333';

    private const string FOURTH_ID = '44444444-4444-4444-8444-444444444444';

    private const string FIFTH_ID = '55555555-5555-4555-8555-555555555555';

    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new InMemoryUserRepository;
        $this->app->instance(UserRepository::class, $this->users);
    }

    public function test_it_returns_ok_with_the_top_users_ordered_by_score(): void
    {
        $this->saveFiveUsers();

        $response = $this->getJson('/ranking?type=top2');

        $response->assertExactJson([
            'status_code' => 200,
            'message' => self::SUCCESS_MESSAGE,
            'result' => [
                ['id' => self::FIFTH_ID, 'score' => 500],
                ['id' => self::FOURTH_ID, 'score' => 400],
            ],
        ]);
    }

    public function test_it_returns_ok_with_the_users_around_the_given_position(): void
    {
        $this->saveFiveUsers();

        $response = $this->getJson('/ranking?type=at3/1');

        $response->assertExactJson([
            'status_code' => 200,
            'message' => self::SUCCESS_MESSAGE,
            'result' => [
                ['id' => self::FOURTH_ID, 'score' => 400],
                ['id' => self::THIRD_ID, 'score' => 300],
                ['id' => self::SECOND_ID, 'score' => 200],
            ],
        ]);
    }

    public function test_it_returns_404_when_there_are_no_users(): void
    {
        $response = $this->getJson('/ranking?type=top5');

        $response->assertExactJson([
            'status_code' => 404,
            'message' => self::NOT_FOUND_MESSAGE,
            'result' => [],
        ]);
    }

    public function test_it_returns_404_when_the_position_is_beyond_the_last_user(): void
    {
        $this->saveFiveUsers();

        $response = $this->getJson('/ranking?type=at99/1');

        $response->assertExactJson([
            'status_code' => 404,
            'message' => self::NOT_FOUND_MESSAGE,
            'result' => [],
        ]);
    }

    public function test_it_returns_422_when_type_is_missing(): void
    {
        $response = $this->getJson('/ranking');

        $this->assertTypeValidationError($response, self::REQUIRED_TYPE_MESSAGE);
    }

    public function test_it_returns_422_when_type_is_not_a_ranking_type(): void
    {
        $response = $this->getJson('/ranking?type=abc');

        $this->assertTypeValidationError($response, self::INVALID_TYPE_MESSAGE);
    }

    public function test_it_returns_422_when_top_has_no_number(): void
    {
        $response = $this->getJson('/ranking?type=top');

        $this->assertTypeValidationError($response, self::INVALID_TYPE_MESSAGE);
    }

    public function test_it_returns_422_when_at_has_no_range(): void
    {
        $response = $this->getJson('/ranking?type=at5');

        $this->assertTypeValidationError($response, self::INVALID_TYPE_MESSAGE);
    }

    public function test_it_returns_422_when_top_includes_a_range(): void
    {
        $response = $this->getJson('/ranking?type=top5/3');

        $this->assertTypeValidationError($response, self::INVALID_TYPE_MESSAGE);
    }

    public function test_it_accepts_ranking_types_in_any_case(): void
    {
        $this->saveFiveUsers();

        $top = $this->getJson('/ranking?type=TOP2');
        $at = $this->getJson('/ranking?type=At3/1');

        $top->assertStatus(200);
        self::assertSame([500, 400], array_column($top->json('result'), 'score'));

        $at->assertStatus(200);
        self::assertSame([400, 300, 200], array_column($at->json('result'), 'score'));
    }

    private function saveFiveUsers(): void
    {
        $this->saveUser(self::FIRST_ID, 100);
        $this->saveUser(self::SECOND_ID, 200);
        $this->saveUser(self::THIRD_ID, 300);
        $this->saveUser(self::FOURTH_ID, 400);
        $this->saveUser(self::FIFTH_ID, 500);
    }

    private function saveUser(string $id, int $score): void
    {
        $this->users->save(new User(
            new UserId($id),
            new AbsoluteScore(ScorePoints::create($score))
        ));
    }

    private function assertTypeValidationError(TestResponse $response, string $message): void
    {
        $response->assertStatus(422);
        $response->assertExactJson([
            'status_code' => 422,
            'message' => $message,
        ]);
    }
}
