<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class AccountService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateProfilePictureKey(User $user, string $key): void
    {
        $user->setProfilePictureKey($key);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}