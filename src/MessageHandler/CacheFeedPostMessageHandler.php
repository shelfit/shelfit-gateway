<?php

namespace App\MessageHandler;

use App\Message\CacheFeedPostMessage;
use App\Repository\FeedPostRepository;
use App\Repository\FollowRepository;
use App\Service\FeedCacheService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CacheFeedPostMessageHandler
{
    private const BATCH_SIZE = 1000;

    public function __construct(
        private FeedCacheService $feedCacheService,
        private FeedPostRepository $feedPostRepository,
        private FollowRepository $followRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CacheFeedPostMessage $message): void
    {
        $feedPost = $this->feedPostRepository->find($message->getFeedPostId());

        if ($feedPost === null) {
            $this->logger->error("Non-existent feed post id {$message->getFeedPostId()} in CacheFeedPostMessageHandler");
            return;
        }

        $postId = $feedPost->getId();
        $timestamp = $feedPost->getCreatedAt()->getTimestamp();
        foreach ($this->followRepository->getUserFollowers($feedPost->getUser()->getId(), self::BATCH_SIZE) as $followerBatch) {
            $this->feedCacheService->cacheFeedPosts($followerBatch, $postId, $timestamp);
        }
    }
}