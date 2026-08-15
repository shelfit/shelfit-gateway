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
        $statuses = array_intersect(
            ReadLogStatus::ALL_STATUSES,
            ($args->offsetGet('statuses') ?? ReadLogStatus::ALL_STATUSES)
        );

        $logs = [];
        foreach ($statuses as $status) {
            $logs = array_merge($logs, $this->readLogRepository->getUserReadLogs($value, [$status], $paginationSortDto));
        }

        return $logs;
    }
}