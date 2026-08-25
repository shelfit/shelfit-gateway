<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\Book\Book;
use App\Entity\ReadLog;
use App\Entity\ReadLogStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReadLog>
 */
class ReadLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReadLog::class);
    }

    /**
     * @param string[] $statuses
     * @param string[] $allowedVisibilities
     * @return ReadLog[]
     */
    public function getUserReadLogs(
        User $user,
        array $statuses,
        array $allowedVisibilities,
        PaginationSortDto $paginationSortDto
    ): array
    {
        return $this->createQueryBuilder('rl')
            ->innerJoin('rl.book', 'b')
            ->where('rl.user = :user')
            ->andWhere('rl.status in (:statuses)')
            ->andWhere('b.visibility in (:allowedVisibilities)')
            ->setParameter('user', $user)
            ->setParameter('statuses', $statuses)
            ->setParameter('allowedVisibilities', $allowedVisibilities)
            ->setFirstResult($paginationSortDto->getOffset())
            ->setMaxResults($paginationSortDto->getLimit())
            ->orderBy("rl.{$paginationSortDto->getSortField()}", $paginationSortDto->getSortDirection())
            ->getQuery()
            ->getResult();
    }
}
