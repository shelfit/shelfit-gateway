<?php

namespace App\GraphQL\Resolver;

use App\DTO\Common\PaginationSortDto;
use App\Entity\User;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\FollowRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class FollowResolver implements QueryInterface
{
    use PaginatedResolverTrait;

    public function __construct(
        private FollowRepository $followRepository,
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
}