<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class AccountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileStorageService $fileStorageService,
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
}