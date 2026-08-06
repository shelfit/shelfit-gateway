<?php

namespace App\Controller;

use App\Repository\AccountActivationTokenRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly AccountActivationTokenRepository $accountActivationTokenRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/accounts/activate', methods: ['POST'])]
    public function activateAccount(Request $req): JsonResponse
    {
        $token = $req->toArray()['token'] ?? null;

        if ($token === null) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Activation token is required'
            ], 400);
        }

        $activationToken = $this->accountActivationTokenRepository->findOneBy(['tokenHash' => hash('sha256', $token)]);

        if ($activationToken === null || $activationToken->isExpired()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Expired or invalid activation token'
            ], 400);
        }

        if ($activationToken->getConsumedAt() !== null) {
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Account already activated, log in'
            ]);
        }

        $activationToken->setConsumedAt(new DateTimeImmutable());

        $user = $activationToken->getUser();
        $user->setActivated(true);

        $this->entityManager->persist($user);
        $this->entityManager->persist($activationToken);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Account successfully activated'
        ]);
    }
}