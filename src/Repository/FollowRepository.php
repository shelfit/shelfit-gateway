<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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
}
