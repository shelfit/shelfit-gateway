<?php

namespace App\Repository;

use App\DTO\Common\PaginationSortDto;
use App\Entity\Comment;
use App\Entity\FeedPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function updateNumReplies(Comment $comment, int $delta): int
    {
        return $this->createQueryBuilder('c')
            ->update()
            ->set('c.numReplies', 'c.numReplies + :delta')
            ->where('c.id = :id')
            ->setParameter('id', $comment->getId())
            ->setParameter('delta', $delta)
            ->getQuery()
            ->execute();
    }

    public function updateNumLikes(Comment $comment, int $delta): int
    {
        return $this->createQueryBuilder('c')
            ->update()
            ->set('c.numLikes', 'c.numLikes + :delta')
            ->where('c.id = :id')
            ->setParameter('id', $comment->getId())
            ->setParameter('delta', $delta)
            ->getQuery()
            ->execute();
    }

    /**
     * @return Comment[]
     */
    public function getRootComments(FeedPost $post, PaginationSortDto $paginationSortDto): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.feedPost = :postId')
            ->andWhere('c.parent is null')
            ->setParameter('postId', $post->getId())
            ->orderBy('c.'.$paginationSortDto->getSortField(), $paginationSortDto->getSortDirection())
            ->setMaxResults($paginationSortDto->getLimit())
            ->setFirstResult($paginationSortDto->getOffset())
            ->getQuery()
            ->execute();
    }

    /**
     * @param Comment[] $comments
     * @return Comment[]
     */
    public function getRepliesToComments(array $comments, ?PaginationSortDto $paginationSortDto = null): array
    {
        if (empty($comments)) {
            return [];
        }

        $qb = $this->createQueryBuilder('c');

        foreach ($comments as $i => $comment) {
            $qb->orWhere("c.path like :rootPath$i")
                ->setParameter(":rootPath$i", "{$comment->getPath()}.%");
        }

        if ($paginationSortDto !== null) {
            $qb->setFirstResult($paginationSortDto->getOffset())
                ->setMaxResults($paginationSortDto->getLimit());
        }

        return $qb->orderBy('c.path', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
