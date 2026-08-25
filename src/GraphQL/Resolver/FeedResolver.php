<?php

namespace App\GraphQL\Resolver;

use App\Entity\FeedPost;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\FeedPostRepository;
use App\Security\LoggedInUserAwareTrait;
use App\Service\FeedService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class FeedResolver implements QueryInterface
{
    use LoggedInUserAwareTrait, PaginatedResolverTrait;

    public function __construct(
        private Security    $security,
        private FeedService $feedPostService,
        private FeedPostRepository $feedPostRepository,
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

        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt');
        return $this->feedPostService->getFeed($user, $paginationSortDto->getOffset());
    }

    public function resolveFeedPost(Argument $args): FeedPost
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $feedPost = $this->feedPostRepository->findOneBy([
            'id' => (int)$args->offsetGet('feedPostId'),
            'deleted' => false
        ]);
        if ($feedPost === null) {
            throw new UserError('feed.post.not.found');
        }

        return $feedPost;
    }
}