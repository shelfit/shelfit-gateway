<?php

namespace App\GraphQL\Mutation;

use App\DTO\CommentDto;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\FeedPostRepository;
use App\Security\LoggedInUserAwareTrait;
use App\Service\AuthorizationService;
use App\Service\CommentService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class CommentMutation implements MutationInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private Security $security,
        private FeedPostRepository $feedPostRepository,
        private CommentService $commentService,
        private CommentRepository $commentRepository,
        private AuthorizationService $authorizationService,
    ) {
    }

    public function createComment(Argument $args): Comment
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $input = $args->offsetGet('createCommentInput');

        $post = $this->feedPostRepository->find($input['postId']);
        if ($post === null) {
            throw new UserError('no.post');
        }

        $parent = null;
        if (($input['parentId'] ?? null) !== null) {
            $parent = $this->commentRepository->find($input['parentId']);
            if ($parent === null) {
                throw new UserError('no.parent.comment');
            }
        }

        $commentDto = (new CommentDto())
            ->setText($input['text'])
            ->setUser($user)
            ->setFeedPost($post)
            ->setParent($parent);

        try {
            return $this->commentService->createComment($commentDto);
        } catch (RuntimeException $e) {
            throw new UserError($e->getMessage());
        }
    }

    public function editComment(Argument $args): Comment
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $comment = $this->commentRepository->find($args->offsetGet('commentId'));
        if ($comment === null) {
            throw new UserError('no.comment');
        }

        if (!$this->authorizationService->authorizeResourceOwnership($comment->getUser()->getId(), $user)) {
            throw new UserError('not.authorized');
        }

        return $this->commentService->editComment($comment, $args->offsetGet('text'));
    }

    public function likeComment(Argument $args): Comment
    {
        return $this->commentLikeCountEdit($args, +1);
    }

    public function unlikeComment(Argument $args): Comment
    {
        return $this->commentLikeCountEdit($args, -1);
    }

    private function commentLikeCountEdit(Argument $args, int $delta): Comment
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $comment = $this->commentRepository->find($args->offsetGet('commentId'));
        if ($comment === null) {
            throw new UserError('no.comment');
        }

        $this->commentService->updateLikeCount($comment, $delta);
        return $comment;
    }
}