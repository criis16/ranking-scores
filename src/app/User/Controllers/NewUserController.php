<?php

namespace App\User\Controllers;

use App\User\Application\AddScoreDTO;
use App\User\Application\NewUserUseCase;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class NewUserController
{
    public function __construct(private NewUserUseCase $useCase)
    {
    }

    public function __invoke(AddScoreRequest $request): JsonResponse
    {
        try {
            $this->useCase->__invoke($this->buildDTO($request->validated()));
            return $this->response('The user\'s score has been successfully saved', Response::HTTP_OK);
        } catch (\InvalidArgumentException $invalidArgument) {
            return $this->response($invalidArgument->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $throwable) {
            return $this->response('Error saving user score: ' . $throwable->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function buildDTO(array $data): AddScoreDTO
    {
        if (isset($data['total'])) {
            return AddScoreDTO::absolute($data['userId'], $data['total']);
        }

        preg_match('/^([+\-])(\d+)$/', $data['score'], $matches);

        return AddScoreDTO::relative($data['userId'], (int)$matches[2], $matches[1]);
    }

    private function response(string $message, int $statusCode): JsonResponse
    {
        return new JsonResponse(['status_code' => $statusCode, 'message' => $message], $statusCode);
    }
}
