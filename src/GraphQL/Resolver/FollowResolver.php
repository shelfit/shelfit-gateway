<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Repository\FollowRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class FollowResolver implements QueryInterface
{
    public function __construct(
        private FollowRepository $followRepository,
    ) {
    }

    /**
     * @return array{users: User[], count: int}
     */
    public function followers(Argument $args, User $value): array
    {
        $limit = (int)($args->offsetGet('limit') ?? 10);
        $offset = (int)($args->offsetGet('offset') ?? 0);

        return [
            'users' => $this->followRepository->getFollowersByUser($value, $limit, $offset),
            'count' => $this->followRepository->getFollowerCountByUser($value),
        ];
    }

    /**
     * @return array{users: User[], count: int}
     */
    public function following(Argument $args, User $value): array
    {
        $limit = (int)($args->offsetGet('limit') ?? 10);
        $offset = (int)($args->offsetGet('offset') ?? 0);

        return [
            'users' => $this->followRepository->getFollowingByUser($value, $limit, $offset),
            'count' => $this->followRepository->getFollowingCountByUser($value),
        ];
    }
}