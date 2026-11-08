<?php

namespace Tests\User\EndToEnd;

use Illuminate\Support\Facades\DB;
use PDO;
use Tests\TestCase;

final class RankingEndpointE2ETest extends TestCase
{
    private const string SUCCESS_MESSAGE = 'Users found successfully';

    private const string NOT_FOUND_MESSAGE = 'No users found';

    private static bool $schemaReady = false;

    private PDO $applicationConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();

        $this->applicationConnection = $this->app->make(PDO::class);
        $this->applicationConnection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->applicationConnection->inTransaction()) {
            $this->applicationConnection->rollBack();
        }

        parent::tearDown();
    }

    public function test_it_ranks_the_users_scored_through_the_endpoint(): void
    {
        $this->scoreUser('11111111-1111-4111-8111-111111111111', 100);
        $this->scoreUser('22222222-2222-4222-8222-222222222222', 300);
        $this->scoreUser('33333333-3333-4333-8333-333333333333', 200);

        $response = $this->getJson('/ranking?type=top2');

        $response->assertExactJson([
            'status_code' => 200,
            'message' => self::SUCCESS_MESSAGE,
            'result' => [
                ['id' => '22222222-2222-4222-8222-222222222222', 'score' => 300],
                ['id' => '33333333-3333-4333-8333-333333333333', 'score' => 200],
            ],
        ]);
    }

    public function test_it_returns_404_when_no_user_has_been_scored(): void
    {
        $this->applicationConnection->exec('DELETE FROM app_users');

        $response = $this->getJson('/ranking?type=top5');

        $response->assertExactJson([
            'status_code' => 404,
            'message' => self::NOT_FOUND_MESSAGE,
            'result' => [],
        ]);
    }

    private function scoreUser(string $id, int $total): void
    {
        $this->postJson(sprintf('/user/%s/score', $id), ['total' => $total])
            ->assertStatus(200);
    }

    private function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }

        $connection = DB::connection();

        if (! $connection->getSchemaBuilder()->hasTable('app_users')) {
            (require database_path('migrations/2026_10_31_000001_create_app_users_table.php'))->up();
        }

        self::$schemaReady = true;
    }
}
