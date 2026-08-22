<?php

namespace App\GraphQL\Resolver;

use App\Entity\FeedPost;
use App\Entity\FeedPostLike;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\FeedPostLikeRepository;
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
        private FeedPostLikeRepository $feedPostLikeRepository,
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

    public function resolvePostLikeCount(FeedPost $value): int
    {
        return $this->feedPostLikeRepository->getFeedPostLikeCount($value);
    }

    /**
     * @return FeedPostLike[]
     */
    public function resolvePostLikes(Argument $args, FeedPost $feedPost): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt', 'desc');
        return $this->feedPostLikeRepository->getFeedPostLikes($feedPost, $paginationSortDto);
    }
}