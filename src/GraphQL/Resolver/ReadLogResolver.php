<?php

namespace App\GraphQL\Resolver;

use App\Entity\ReadLog;
use App\Entity\ReadLogPageUpdate;
use App\Repository\ReadLogRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class ReadLogResolver implements QueryInterface
{
    public function __construct(
        private ReadLogRepository $readLogRepository,
    ) {
    }

    public function readLog(Argument $args): ?ReadLog
    {
        return $this->readLogRepository->find($args->offsetGet('id'));
    }

    /**
     * @return ReadLogPageUpdate[]
     */
    public function resolvePageUpdates(ReadLog $value): array
    {
        return $value->getReadLogPageUpdates()->toArray();
    }
}