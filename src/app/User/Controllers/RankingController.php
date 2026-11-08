<?php

namespace App\User\Controllers;

use App\User\Application\Ranking\GetRankingUseCase;
use App\User\Application\Ranking\RankingDTO;
use App\User\Domain\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class RankingController
{
    public function __construct(private GetRankingUseCase $useCase)
    {
    }

    public function __invoke(RankingRequest $request): JsonResponse
    {
        try {
            $users = $this->useCase->__invoke($this->buildDTO($request->validated('type')));

            if (empty($users)) {
                return $this->response('No users found', Response::HTTP_NOT_FOUND, []);
            }

            return $this->response(
                'Users found successfully',
                Response::HTTP_OK,
                array_map(fn(User $user): array => $this->adapt($user), $users)
            );
        } catch (\InvalidArgumentException $invalidArgument) {
            return $this->response($invalidArgument->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $throwable) {
            return $this->response('Error retrieving ranking: ' . $throwable->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function buildDTO(string $type): RankingDTO
    {
        $type = strtolower($type);

        if (preg_match('/^top(\d+)$/', $type, $matches)) {
            return RankingDTO::top((int)$matches[1]);
        }

        if (preg_match('/^at(\d+)\/(\d+)$/', $type, $matches)) {
            return RankingDTO::at((int)$matches[1], (int)$matches[2]);
        }

        throw new \InvalidArgumentException('Invalid type value: ' . $type);
    }

    private function adapt(User $user): array
    {
        return [
            'id' => $user->id()->value(),
            'score' => $user->score()->points()->value(),
        ];
    }

    private function response(string $message, int $statusCode, ?array $result = null): JsonResponse
    {
        $payload = ['status_code' => $statusCode, 'message' => $message];

        if (!is_null($result)) {
            $payload['result'] = $result;
        }

        return new JsonResponse($payload, $statusCode);
    }
}
