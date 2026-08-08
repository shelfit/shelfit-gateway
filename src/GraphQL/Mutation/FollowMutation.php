<?php

namespace App\GraphQL\Mutation;

use App\Security\LoggedInUserAwareTrait;
use App\Service\FollowService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class FollowMutation implements MutationInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private Security $security,
        private FollowService $followService,
    ) {
    }

    public function follow(Argument $args): bool
    {
        $followedUserId = $args->offsetGet('followedUserId');

        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        return $this->followService->follow($followedUserId, $user);
    }

    public function unfollow(Argument $args): bool
    {
        $unfollowedUserId = $args->offsetGet('unfollowedUserId');

        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        return $this->followService->unfollow($unfollowedUserId, $user);
    }
}