<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
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
     * @param ReadLogStatus[] $statuses
     * @return ReadLog[]
     */
    public function getUserReadLogs(User $user, array $statuses, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('rl')
            ->where('rl.user = :user')
            ->andWhere('rl.status in (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', $statuses)
            ->setFirstResult($paginationSortDto->getOffset())
            ->setMaxResults($paginationSortDto->getLimit())
            ->orderBy("rl.{$paginationSortDto->getSortField()}", $paginationSortDto->getSortDirection())
            ->getQuery()
            ->getResult();
    }
}
