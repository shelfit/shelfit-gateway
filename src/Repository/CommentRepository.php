<?php

namespace App\Repository;

use App\Entity\Comment;
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
}
