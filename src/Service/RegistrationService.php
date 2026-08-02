<?php

namespace App\Service;

use App\DTO\UserDto;
use App\Entity\AccountActivationToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegistrationService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function register(UserDto $userDto): User
    {
        $user = (new User())
            ->setUsername($userDto->getUsername())
            ->setEmail($userDto->getEmail());
        $user->setPassword($this->passwordHasher->hashPassword($user, $userDto->getPassword()));

        $activationTokenPlain = bin2hex(random_bytes(16));

        $accountActivationToken = (new AccountActivationToken())
            ->setUser($user)
            ->setTokenHash(hash('sha256', $activationTokenPlain))
            ->setIssuedAt(new DateTimeImmutable())
            ->setExpiresAt(new DateTimeImmutable('+1 day'));

        $this->entityManager->persist($user);
        $this->entityManager->persist($accountActivationToken);
        $this->entityManager->flush();

        return $user;
    }
}