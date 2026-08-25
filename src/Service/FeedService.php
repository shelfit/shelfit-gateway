<?php

namespace App\Service;

use App\Entity\FeedPost;
use App\Entity\FeedPostLike;
use App\Entity\FeedPostType;
use App\Entity\ReadLog;
use App\Entity\User;
use App\Message\CacheFeedPostMessage;
use App\Message\RemoveFeedPostFromCacheMessage;
use App\Repository\FeedPostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RedisException;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class FeedService
{
    private const FEED_PAGE_LIMIT = 50;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private FeedCacheService $feedCacheService,
        private FeedPostRepository $feedPostRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function createTextPost(string $text, User $author): FeedPost
    {
        $feedPost = (new FeedPost())
            ->setUser($author)
            ->setText($text)
            ->setType(FeedPostType::TYPE_TEXT)
            ->setDeleted(false)
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($feedPost);
        $this->entityManager->flush();

        $this->bus->dispatch(new CacheFeedPostMessage($feedPost->getId()));
        return $feedPost;
    }

    public function reviewFeedPostAlreadyExists(User $author, ReadLog $readLog): bool
    {
        return $this->feedPostRepository->findOneBy([
            'user' => $author,
            'log' => $readLog,
            'type' => FeedPostType::TYPE_REVIEW
        ]) !== null;
    }

    public function createPostFromReadLog(ReadLog $log, User $author, string $type): FeedPost
    {
        $feedPost = (new FeedPost())
            ->setLog($log)
            ->setUser($author)
            ->setType($type)
            ->setDeleted(false)
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($feedPost);
        $this->entityManager->flush();
        return $feedPost;
    }

    public function deleteFeedPost(FeedPost $feedPost): bool
    {
        $user = $feedPost->getUser();

        $feedPost->setDeleted(true)
            ->setDeletedAt(new DateTimeImmutable());
        $this->entityManager->persist($feedPost);
        $this->entityManager->flush();

        $this->bus->dispatch(new RemoveFeedPostFromCacheMessage($feedPost->getId(), $user->getId()));
        return true;
    }

    /**
     * @return FeedPost[]
     */
    public function getFeed(User $user, int $offset): array
    {
        try {
            $ids = $this->feedCacheService->getFeedForUser($user->getId(), self::FEED_PAGE_LIMIT, $offset);

            if (!empty($ids)) {
                $posts = $this->feedPostRepository->findBy(['id' => $ids, 'deleted' => false]);
                usort($posts, static fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
                return $posts;
            }

            if ($offset > 0) {
                return [];
            }
        } catch (RedisException $e) {
            $this->logger->error("Error reading feed from cache", ['exception' => $e]);
        }

        $feed = $this->feedPostRepository->getFeedForUser($user, FeedCacheService::FEED_SIZE_LIMIT, 0);

        if (empty($feed)) {
            return [];
        }

        try {
            $posts = [];
            foreach ($feed as $post) {
                $posts[$post->getId()] = $post->getCreatedAt()->getTimestamp();
            }
            $this->feedCacheService->buildUserFeed($user->getId(), $posts);
        } catch (RedisException $e) {
            $this->logger->error("Error writing feed to cache", ['exception' => $e]);
        }

        return array_slice($feed, $offset, self::FEED_PAGE_LIMIT);
    }

    public function likeFeedPost(FeedPost $feedPost, User $user): FeedPostLike
    {
        $like = (new FeedPostLike())
            ->setFeedPost($feedPost)
            ->setUser($user)
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($like);
        $this->entityManager->flush();
        return $like;
    }
}