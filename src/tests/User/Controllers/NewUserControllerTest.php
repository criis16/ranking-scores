<?php

namespace Tests\User\Controllers;

use App\User\Domain\UserRepository;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\User\Application\InMemoryUserRepository;

final class NewUserControllerTest extends TestCase
{
    private const string USER_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string SUCCESS_MESSAGE = "The user's score has been successfully saved";

    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = new InMemoryUserRepository();
        $this->app->instance(UserRepository::class, $this->users);
    }

    public function test_it_returns_ok_and_saves_absolute_score_for_new_user(): void
    {
        $response = $this->postJson(
            sprintf('/user/%s/score', self::USER_ID),
            ['total' => 150]
        );

        $this->assertScoreSaved($response, 200, self::SUCCESS_MESSAGE);
        self::assertSame(150, $this->scoreOf(self::USER_ID));
    }

    public function test_it_accumulates_relative_score_across_requests(): void
    {
        $this->postJson(sprintf('/user/%s/score', self::USER_ID), ['total' => 100])
            ->assertStatus(200);

        $response = $this->postJson(sprintf('/user/%s/score', self::USER_ID), ['score' => '+10']);

        $this->assertScoreSaved($response, 200, self::SUCCESS_MESSAGE);
        self::assertSame(110, $this->scoreOf(self::USER_ID));
    }

    public function test_it_returns_422_when_id_is_not_a_uuid(): void
    {
        $response = $this->postJson('/user/not-a-uuid/score', ['total' => 10]);

        $this->assertValidationError($response);
    }

    public function test_it_returns_422_when_both_total_and_score_are_provided(): void
    {
        $response = $this->postJson(
            sprintf('/user/%s/score', self::USER_ID),
            ['total' => 10, 'score' => '+5']
        );

        $this->assertValidationError($response);
    }

    public function test_it_returns_422_when_no_score_field_is_provided(): void
    {
        $response = $this->postJson(sprintf('/user/%s/score', self::USER_ID), []);

        $this->assertValidationError($response);
    }

    public function test_it_returns_422_when_relative_score_format_is_invalid(): void
    {
        $response = $this->postJson(
            sprintf('/user/%s/score', self::USER_ID),
            ['score' => '10']
        );

        $this->assertValidationError($response);
    }

    private function assertScoreSaved(TestResponse $response, int $statusCode, string $message): void
    {
        $response->assertStatus($statusCode);
        $response->assertJson([
            'status_code' => $statusCode,
            'message' => $message,
        ]);
    }

    private function assertValidationError(TestResponse $response): void
    {
        $response->assertStatus(422);
        $response->assertJsonStructure(['status_code', 'message']);
        $response->assertJson(['status_code' => 422]);

        self::assertSame([], $this->users->findAll());
    }

    private function scoreOf(string $id): int
    {
        $users = array_filter(
            $this->users->findAll(),
            static fn($user): bool => $user->id()->value() === $id
        );

        return reset($users)->score()->points()->value();
    }
}
