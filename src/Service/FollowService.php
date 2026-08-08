<?php

namespace App\Service;

use App\Entity\Follow;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

readonly class FollowService
{
    public function __construct(
        private UserRepository $userRepository,
        private FollowRepository $followRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function follow(int $followedUserId, User $follower): bool
    {
        $followedUser = $this->userRepository->find($followedUserId);

        if ($followedUser === null || $followedUser->getId() === $follower->getId()) {
            return false;
        }

        $follow = (new Follow())
            ->setFollower($follower)
            ->setFollowing($followedUser)
            ->setCreatedAt(new DateTimeImmutable());

        try {
            $this->entityManager->persist($follow);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        return true;
    }

    public function unfollow(int $followedUserId, User $follower): bool
    {
        $follow = $this->followRepository->getFollowPair($follower->getId(), $followedUserId);

        if ($follow === null) {
            return false;
        }

        $this->entityManager->remove($follow);
        $this->entityManager->flush();
        return true;
    }
}