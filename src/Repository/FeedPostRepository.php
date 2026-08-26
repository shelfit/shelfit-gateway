<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\Book\Book;
use App\Entity\FeedPost;
use App\Entity\FeedPostType;
use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedPost>
 */
class FeedPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedPost::class);
    }

    /**
     * @return FeedPost[]
     */
    public function getFeedForUser(User $user, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('fp')
            ->innerJoin(Follow::class, 'f', Join::WITH, 'fp.user = f.following')
            ->where('f.follower = :user')
            ->andWhere('fp.deleted = 0')
            ->setParameter('user', $user)
            ->orderBy('fp.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return FeedPost[]
     */
    public function getReviewsForBook(Book $book, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('fp')
            ->innerJoin('fp.log', 'l')
            ->andWhere('l.book = :book')
            ->andWhere('fp.deleted = 0')
            ->andWhere('fp.type = :type')
            ->setParameter('book', $book)
            ->setParameter('type', FeedPostType::TYPE_REVIEW)
            ->orderBy('fp.'.$paginationSortDto->getSortField(), $paginationSortDto->getSortDirection())
            ->setMaxResults($paginationSortDto->getLimit())
            ->setFirstResult($paginationSortDto->getOffset())
            ->getQuery()
            ->getResult();
    }
}
