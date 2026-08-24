<?php

namespace App\Service;

use App\DTO\CommentDto;
use App\DTO\CommentThreadDto;
use App\DTO\Common\PaginationSortDto;
use App\Entity\Comment;
use App\Entity\FeedPost;
use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class CommentService
{
    private const MAX_THREAD_DEPTH = 20;
    private const MAX_REPLIES_IN_VISIBLE_THREAD = 20;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommentRepository $commentRepository,
    ) {
    }

    /**
     * @return CommentThreadDto[]
     */
    public function getCommentThread(FeedPost $feedPost, PaginationSortDto $paginationSortDto): array
    {
        $rootComments = $this->commentRepository->getRootComments($feedPost, $paginationSortDto);
        $replies = $this->commentRepository->getRepliesToComments($rootComments);

        $repliesGrouped = [];
        foreach ($replies as $reply) {
            $root = explode('.', $reply->getPath())[0];
            $repliesGrouped[$root][] = $reply;
        }

        $result = [];
        foreach ($rootComments as $rootComment) {
            $replyList = $repliesGrouped[$rootComment->getPath()] ?? [];

            if (count($replyList) > self::MAX_REPLIES_IN_VISIBLE_THREAD) {
                $result[] = (new CommentThreadDto())
                    ->setRoot($rootComment)
                    ->setReplies(array_slice($replyList, 0, self::MAX_REPLIES_IN_VISIBLE_THREAD))
                    ->setHasMoreReplies(true);

                continue;
            }

            $result[] = (new CommentThreadDto())
                ->setRoot($rootComment)
                ->setReplies($replyList)
                ->setHasMoreReplies(false);
        }

        return $result;
    }

    /**
     * @return Comment[]
     */
    public function getRepliesToComment(Comment $comment, PaginationSortDto $paginationSortDto): array
    {
        return $this->commentRepository->getRepliesToComments([$comment], $paginationSortDto);
    }

    /**
     * @throws RuntimeException
     */
    public function createComment(CommentDto $commentDto): Comment
    {
        $parent = $commentDto->getParent();

        $this->entityManager->beginTransaction();

        $comment = (new Comment())
            ->setText($commentDto->getText())
            ->setUser($commentDto->getUser())
            ->setFeedPost($commentDto->getFeedPost())
            ->setCreatedAt(new DateTimeImmutable());

        if ($parent !== null) {
            $comment->setParent($parent);

            if ($parent->getDepth() >= self::MAX_THREAD_DEPTH) {
                $this->entityManager->rollback();
                throw new RuntimeException('thread.over.max.depth');
            }
            $comment->setDepth($parent->getDepth() + 1);
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $comment->setPath($this->resolvePath($comment, $parent));

        if ($parent !== null) {
            $this->commentRepository->updateNumReplies($parent, +1);
        }

        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->entityManager->commit();
        return $comment;
    }

    private function resolvePath(Comment $comment, ?Comment $parent): string
    {
        $idPadded = str_pad($comment->getId(), 10, '0', STR_PAD_LEFT);

        if ($parent === null) {
            return $idPadded;
        }

        return $parent->getPath() . "." . $idPadded;
    }

    public function editComment(Comment $comment, string $newText): Comment
    {
        $comment->setText($newText);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
        return $comment;
    }

    public function updateLikeCount(Comment $comment, int $delta): void
    {
        if ($comment->getNumLikes() === 0 && $delta < 0) {
            return;
        }

        $this->commentRepository->updateNumLikes($comment, $delta);
    }
}