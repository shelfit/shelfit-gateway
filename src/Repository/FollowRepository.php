<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Generator;

/**
 * @extends ServiceEntityRepository<Follow>
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    public function getFollowersByUser(User $user, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('f')
            ->select('u')
            ->innerJoin(User::class, 'u', Join::WITH, 'f.follower = u')
            ->where('f.following = :user')
            ->setParameter('user', $user)
            ->setFirstResult($paginationSortDto->getOffset())
            ->setMaxResults($paginationSortDto->getLimit())
            ->getQuery()
            ->getResult();
    }

    public function getFollowerCountByUser(User $user): int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.following)')
            ->where('f.following = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFollowingByUser(User $user, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('f')
            ->select('u')
            ->innerJoin(User::class, 'u', Join::WITH, 'f.following = u')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->setFirstResult($paginationSortDto->getOffset())
            ->setMaxResults($paginationSortDto->getLimit())
            ->getQuery()
            ->getResult();
    }

    public function getFollowingCountByUser(User $user): int
    {
        return $this->createQueryBuilder('f')
            ->select('count(f.follower)')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFollowPair(int $followerId, int $followingId): ?Follow
    {
        return $this->createQueryBuilder('f')
            ->where('f.follower = :followerId')
            ->andWhere('f.following = :followingId')
            ->setParameter('followerId', $followerId)
            ->setParameter('followingId', $followingId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getUserFollowers(int $userId, int $batchSize): Generator
    {
        $lastId = 0;

        do {
            $followerIds = $this->createQueryBuilder('f')
                ->select('identity(f.follower) as id')
                ->where('f.following = :userId')
                ->andWhere('f.follower > :lastId')
                ->setParameter('userId', $userId)
                ->setParameter('lastId', $lastId)
                ->orderBy('f.follower', 'asc')
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getSingleColumnResult();

            if (empty($followerIds)) {
                return;
            }

            $lastId = end($followerIds);
            yield $followerIds;
        } while (count($followerIds) === $batchSize);
    }
}
