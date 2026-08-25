<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\AccountTokenException;
use App\Exception\UserInputValidationException;
use App\Repository\AccountActivationTokenRepository;
use App\Repository\UserRepository;
use App\Service\AccountService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountActivationTokenRepository $accountActivationTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AccountService $accountService,
        private readonly UserRepository $userRepository,
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

    #[Route('/api/accounts/forgot-password', methods: ['POST'])]
    public function forgotPassword(Request $req): JsonResponse
    {
        $email = $req->toArray()['email'] ?? null;
        if ($email === null) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Email is required'
            ]);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user === null) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }

        $this->accountService->sendPasswordResetEmail($user);
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Password reset email sent to '.$user->getEmail()
        ]);
    }

    #[Route('/api/accounts/reset-password', methods: ['POST'])]
    public function resetPassword(Request $req): JsonResponse
    {
        $token = $req->toArray()['token'] ?? null;
        $newPassword = $req->toArray()['newPassword'] ?? null;

        if ($token === null || $newPassword === null) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Both reset token and new password are required'
            ], 400);
        }

        try {
            $this->accountService->resetPassword($newPassword, $token);
        } catch (AccountTokenException|UserInputValidationException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ]);
    }
}