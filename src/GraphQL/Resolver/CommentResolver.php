<?php

namespace App\GraphQL\Resolver;

use App\DTO\CommentThreadDto;
use App\Entity\Comment;
use App\Entity\FeedPost;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;

readonly class CommentResolver implements QueryInterface
{
    use PaginatedResolverTrait;

    public function __construct(
        private CommentService $commentService,
        private CommentRepository $commentRepository,
    ) {
    }

    /**
     * @return CommentThreadDto[]
     */
    public function resolvePostComments(Argument $args, FeedPost $value): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt', 'desc');
        return $this->commentService->getCommentThread($value, $paginationSortDto);
    }

    /**
     * @return Comment[]
     */
    public function resolveCommentReplies(Argument $args): array
    {
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt', 'desc');

        $comment = $this->commentRepository->find($args->offsetGet('commentId'));
        if ($comment === null) {
            throw new UserError('no.comment');
        }

        return $this->commentService->getRepliesToComment($comment, $paginationSortDto);
    }
}