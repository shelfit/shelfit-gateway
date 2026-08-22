<?php

namespace App\GraphQL\Resolver;

use App\Entity\ReadLog;
use App\Entity\ReadLogPageUpdate;
use App\Entity\ReadLogStatus;
use App\Entity\User;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\ReadLogPageUpdateRepository;
use App\Repository\ReadLogRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class ReadLogResolver implements QueryInterface
{
    use PaginatedResolverTrait;

    public function __construct(
        private ReadLogRepository $readLogRepository,
        private ReadLogPageUpdateRepository $readLogPageUpdateRepository,
    ) {
    }

    public function readLog(Argument $args): ?ReadLog
    {
        return $this->readLogRepository->find($args->offsetGet('id'));
    }

    /**
     * @return ReadLogPageUpdate[]
     */
    public function resolvePageUpdates(Argument $args, ReadLog $value): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt');
        return $this->readLogPageUpdateRepository->getPageUpdatesForReadLog($value, $paginationSortDto);
    }

    /**
     * @return ReadLog[]
     */
    public function userReadLogs(Argument $args, User $value): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'updatedAt');
        $requestedStatuses = array_intersect(
            ReadLogStatus::ALL_STATUSES,
            ($args->offsetGet('statuses') ?? ReadLogStatus::ALL_STATUSES)
        );

        $logs = $this->readLogRepository->getUserReadLogs($value, $requestedStatuses, $paginationSortDto);

        $resultsByStatus = array_combine($requestedStatuses, array_fill(0, count($requestedStatuses), []));
        foreach ($logs as $log) {
            $resultsByStatus[$log->getStatus()][] = $log;
        }

        return array_merge(...array_values($resultsByStatus));
    }
}