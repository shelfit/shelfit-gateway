<?php

namespace App\GraphQL\Resolver;

use App\Entity\Book\BookVisibility;
use App\Entity\ReadLog;
use App\Entity\ReadLogPageUpdate;
use App\Entity\ReadLogStatus;
use App\Entity\User;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\ReadLogPageUpdateRepository;
use App\Repository\ReadLogRepository;
use App\Security\LoggedInUserAwareTrait;
use App\Service\ReadLogService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class ReadLogResolver implements QueryInterface
{
    use LoggedInUserAwareTrait, PaginatedResolverTrait;

    public function __construct(
        private ReadLogRepository $readLogRepository,
        private ReadLogPageUpdateRepository $readLogPageUpdateRepository,
        private Security $security,
        private ReadLogService $readLogService,
    ) {
    }

    public function readLog(Argument $args): ?ReadLog
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            $user = null;
        }

        $log = $this->readLogRepository->find($args->offsetGet('id'));

        if ($log === null) {
            return null;
        }

        if ($log->getBook()->getVisibility() === BookVisibility::VISIBILITY_PRIVATE) {
            if ($user === null) {
                return null;
            }

            if ($log->getUser()->getId() !== $user->getId()) {
                return null;
            }
        }

        return $log;
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
        try {
            $loggedInUser = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            $loggedInUser = null;
        }

        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'updatedAt');
        $requestedStatuses = array_intersect(
            ReadLogStatus::ALL_STATUSES,
            ($args->offsetGet('statuses') ?? ReadLogStatus::ALL_STATUSES)
        );

        $allowedVisibilities = $loggedInUser !== null && $loggedInUser->getId() === $value->getId()
            ? BookVisibility::ALL_VISIBILITIES
            : [BookVisibility::VISIBILITY_PUBLIC];

        return $this->readLogService->getUserReadLogs($value, $requestedStatuses, $allowedVisibilities, $paginationSortDto);
    }
}