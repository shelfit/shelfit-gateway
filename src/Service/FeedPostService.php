<?php

namespace App\Service;

use App\Entity\FeedPost;
use App\Entity\ReadLog;
use App\Entity\User;
use App\Message\CacheFeedPostMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class FeedPostService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) {
    }

    public function createPostFromReadLog(ReadLog $log, User $author, string $type): FeedPost
    {
        $feedPost = (new FeedPost())
            ->setLog($log)
            ->setUser($author)
            ->setType($type)
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($feedPost);
        $this->entityManager->flush();

        $this->bus->dispatch(new CacheFeedPostMessage($feedPost->getId()));
        return $feedPost;
    }
}