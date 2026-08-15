<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\Book\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function getBooksByIds(array $ids): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Book[]
     */
    public function searchBooks(string $search, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('b')
            ->where('lower(b.title) LIKE :search')
            ->orWhere('lower(b.author) LIKE :search')
            ->setParameter('search', '%'.strtolower($search).'%')
            ->setMaxResults($paginationSortDto->getLimit())
            ->setFirstResult($paginationSortDto->getOffset())
            ->orderBy("b.{$paginationSortDto->getSortField()}", $paginationSortDto->getSortDirection())
            ->getQuery()
            ->getResult();
    }
}
