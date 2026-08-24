<?php

namespace App\Service;

use App\DTO\CommentDto;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

readonly class CommentService
{
    private const MAX_THREAD_DEPTH = 20;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommentRepository $commentRepository,
    ) {
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

            if ($parent->getDepth() === self::MAX_THREAD_DEPTH) {
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