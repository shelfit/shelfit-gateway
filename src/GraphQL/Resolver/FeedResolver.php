<?php

namespace App\GraphQL\Resolver;

use App\Entity\FeedPost;
use App\Security\LoggedInUserAwareTrait;
use App\Service\FeedService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class FeedResolver implements QueryInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private Security    $security,
        private FeedService $feedPostService,
    ) {
    }

    /**
     * @return FeedPost[]
     */
    public function resolveFeed(Argument $args): array
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        return $this->feedPostService->getFeed($user, $args->offsetGet('offset') ?? 0);
    }
}