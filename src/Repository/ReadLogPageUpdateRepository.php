<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\ReadLog;
use App\Entity\ReadLogPageUpdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReadLogPageUpdate>
 */
class ReadLogPageUpdateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReadLogPageUpdate::class);
    }

    /**
     * @return ReadLogPageUpdate[]
     */
    public function getPageUpdatesForReadLog(ReadLog $log, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('pu')
            ->andWhere('pu.log = :readLog')
            ->setParameter('readLog', $log)
            ->orderBy("pu.{$paginationSortDto->getSortField()}", $paginationSortDto->getSortDirection())
            ->setFirstResult($paginationSortDto->getOffset())
            ->setMaxResults($paginationSortDto->getLimit())
            ->getQuery()
            ->getResult();
    }
}
