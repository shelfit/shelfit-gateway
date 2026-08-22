<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\FeedPost;
use App\Entity\FeedPostLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedPostLike>
 */
class FeedPostLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedPostLike::class);
    }

    public function getFeedPostLikeCount(FeedPost $feedPost): int
    {
        return $this->createQueryBuilder('fpl')
            ->select('count(distinct fpl.id)')
            ->where('fpl.feedPost = :feedPost')
            ->setParameter('feedPost', $feedPost)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @return FeedPostLike[]
     */
    public function getFeedPostLikes(FeedPost $feedPost, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('fpl')
            ->where('fpl.feedPost = :feedPost')
            ->setParameter('feedPost', $feedPost)
            ->setFirstResult($paginationSortDto->getOffset())
            ->setMaxResults($paginationSortDto->getLimit())
            ->orderBy("fpl.{$paginationSortDto->getSortField()}", $paginationSortDto->getSortDirection())
            ->getQuery()
            ->getResult();
    }
}
