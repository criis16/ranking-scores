<?php

namespace App\User\Infrastructure\Persistence;

use App\Score\Domain\AbsoluteScore;
use App\Score\Domain\ScorePoints;
use App\Shared\Domain\Criteria\Criteria;
use App\Shared\Infrastructure\Persistence\PdoCriteriaConverter;
use App\User\Domain\User;
use App\User\Domain\UserId;
use App\User\Domain\UserRepository;

final class PgsqlUserRepository implements UserRepository
{
    private const array FIELD_MAP = [
        'id' => 'id',
        'score' => 'score',
    ];

    public function __construct(private readonly \PDO $connection)
    {
    }

    public function save(User $user): void
    {
        $stmt = $this->connection->prepare(<<<'SQL'
            INSERT INTO app_users (id, score, created_at, updated_at)
            VALUES (:id, :score, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ON CONFLICT (id) DO UPDATE SET
                score = EXCLUDED.score,
                updated_at = CURRENT_TIMESTAMP
            SQL
        );

        $stmt->execute([
            'id' => $user->id()->value(),
            'score' => $user->score()->points()->value(),
        ]);
    }

    public function searchByCriteria(Criteria $criteria): array
    {
        $sql = PdoCriteriaConverter::convert($criteria, self::FIELD_MAP);

        $stmt = $this->connection->prepare(sprintf(
            'SELECT id, score FROM app_users %s %s %s',
            $sql['where'],
            $sql['order'],
            $sql['limit']
        ));
        $stmt->execute($sql['params']);

        $users = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $users[] = $this->toDomain($row);
        }

        return $users;
    }

    private function toDomain(array $row): User
    {
        return new User(
            new UserId($row['id']),
            new AbsoluteScore(ScorePoints::create((int)$row['score']))
        );
    }
}
