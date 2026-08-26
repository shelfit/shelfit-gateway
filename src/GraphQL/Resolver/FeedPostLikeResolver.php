<?php

namespace App\GraphQL\Resolver;

use App\Entity\FeedPost;
use App\Entity\FeedPostLike;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\FeedPostLikeRepository;
use App\Security\LoggedInUserAwareTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class FeedPostLikeResolver implements QueryInterface
{
    use LoggedInUserAwareTrait, PaginatedResolverTrait;

    public function __construct(
        private FeedPostLikeRepository $feedPostLikeRepository,
        private Security $security,
    ) {
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

    public function resolvePostLikedByMe(FeedPost $value): ?bool
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            return null;
        }

        return $this->feedPostLikeRepository->getFeedPostLikeByUser($value, $user) !== null;
    }
}
