<?php

namespace App\Service;

use App\DTO\UserDto;
use App\Entity\User;
use App\Exception\UserInputValidationException;
use App\Message\SendEmailMessage;
use App\Exception\AccountTokenException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class AccountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileStorageService $fileStorageService,
        private MessageBusInterface $bus,
        private AccountTokenService $accountTokenService,
        private EmailConfigProvider $emailConfigProvider,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function updateProfilePictureKey(User $user, string $key): void
    {
        $previousKey = $user->getProfilePictureKey();

        if ($previousKey === $key) {
            return;
        }

        $user->setProfilePictureKey($key);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if ($previousKey !== null) {
            $this->fileStorageService->deleteFile($previousKey);
        }
    }

    public function sendPasswordResetEmail(User $user): void
    {
        $token = $this->accountTokenService->createPasswordResetToken($user);

        $config = $this->emailConfigProvider->getConfig();
        $this->bus->dispatch(new SendEmailMessage(
            idempotencyKey: bin2hex(random_bytes(16)),
            subject: $config[EmailConfigProvider::PASSWORD_RESET_EMAIL]['subject'],
            from: $config[EmailConfigProvider::PASSWORD_RESET_EMAIL]['sender'],
            to: $user->getEmail(),
            template: $config[EmailConfigProvider::PASSWORD_RESET_EMAIL]['template'],
            variables: [
                'token' => $token,
            ]
        ));
    }

    /**
     * @throws AccountTokenException
     * @throws UserInputValidationException
     */
    public function resetPassword(string $newPassword, string $token): void
    {
        $accountToken = $this->accountTokenService->validateToken($token);
        $accountToken->setConsumedAt(new DateTimeImmutable());

        if (strlen($newPassword) < UserDto::PASSWORD_MIN_LENGTH) {
            throw new UserInputValidationException('Password must be at least '.UserDto::PASSWORD_MIN_LENGTH.' characters long');
        }

        $user = $accountToken->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));

        $this->entityManager->persist($accountToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}