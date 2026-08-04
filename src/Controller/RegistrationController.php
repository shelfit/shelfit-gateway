<?php

namespace App\Controller;

use App\DTO\UserDto;
use App\Service\RegistrationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly LoggerInterface $logger,
    ){
    }

    #[Route('/api/register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload(validationGroups: UserDto::VALIDATION_GROUP_REGISTER)] UserDto $userDto
    ): JsonResponse
    {
        try {
            $user = $this->registrationService->register($userDto);
        } catch (Throwable $e) {
            $this->logger->error("Registration error", ['exception' => $e->getMessage()]);
            return $this->json(['status' => 'error'], 500);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'userId' => $user->getId(),
            ],
        ]);
    }
}