<?php

namespace App\GraphQL\Resolver;

use App\DTO\Common\PaginationSortDto;
use App\Entity\User;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\FollowRepository;
use App\Security\LoggedInUserAwareTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class FollowResolver implements QueryInterface
{
    use LoggedInUserAwareTrait, PaginatedResolverTrait;

    public function __construct(
        private FollowRepository $followRepository,
        private Security $security,
    ) {
    }

    /**
     * @return array{users: User[], count: int}
     */
    public function followers(Argument $args, User $value): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt');

        return [
            'users' => $this->followRepository->getFollowersByUser($value, $paginationSortDto),
            'count' => $this->followRepository->getFollowerCountByUser($value),
        ];
    }

    /**
     * @return array{users: User[], count: int}
     */
    public function following(Argument $args, User $value): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt');

        return [
            'users' => $this->followRepository->getFollowingByUser($value, $paginationSortDto),
            'count' => $this->followRepository->getFollowingCountByUser($value),
        ];
    }

    public function resolveFollowedByMe(User $value): ?bool
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            return null;
        }

        return $this->followRepository->getFollowPair($user->getId(), $value->getId()) !== null;
    }
}