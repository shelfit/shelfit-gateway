<?php

namespace App\Service;

use App\Entity\AccountToken;
use App\Entity\AccountTokenType;
use App\Entity\User;
use App\Exception\AccountTokenException;
use App\Repository\AccountTokenRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class AccountTokenService
{
    public const PASSWORD_RESET_TOKEN_TTL_MINUTES = 15;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AccountTokenRepository $accountTokenRepository,
    ) {
    }

    public function createPasswordResetToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $issuedAt = new DateTimeImmutable();

        $accountToken = (new AccountToken())
            ->setTokenHash(hash('sha256', $token))
            ->setIssuedAt($issuedAt)
            ->setExpiresAt($issuedAt->add(new DateInterval('PT'.self::PASSWORD_RESET_TOKEN_TTL_MINUTES.'M')))
            ->setType(AccountTokenType::PASSWORD_RESET_TOKEN)
            ->setUser($user);

        $this->entityManager->persist($accountToken);
        $this->entityManager->flush();
        return $token;
    }

    /**
     * @throws AccountTokenException
     */
    public function validateToken(string $token): AccountToken
    {
        $accountToken = $this->accountTokenRepository->findOneBy(['tokenHash' => hash('sha256', $token)]);
        if ($accountToken === null) {
            throw new AccountTokenException('Token not found');
        }

        if ($accountToken->isExpired()) {
            throw new AccountTokenException('Token expired');
        }

        if ($accountToken->getConsumedAt() !== null) {
            throw new AccountTokenException('Token already consumed');
        }

        return $accountToken;
    }
}