<?php

namespace App\GraphQL\Resolver;

use App\Entity\FeedPost;
use App\Entity\FeedPostLike;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\FeedPostLikeRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class FeedPostLikeResolver implements QueryInterface
{
    use PaginatedResolverTrait;

    public function __construct(
        private FeedPostLikeRepository $feedPostLikeRepository,
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
}
