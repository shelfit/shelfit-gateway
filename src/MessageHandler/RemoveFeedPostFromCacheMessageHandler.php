<?php

namespace App\MessageHandler;

use App\Message\RemoveFeedPostFromCacheMessage;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use App\Service\FeedCacheService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class RemoveFeedPostFromCacheMessageHandler
{
    private const BATCH_SIZE = 1000;

    public function __construct(
       private FeedCacheService $feedCacheService,
       private UserRepository $userRepository,
       private FollowRepository $followRepository,
       private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RemoveFeedPostFromCacheMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());
        if ($user === null) {
            $this->logger->error("Non-existent user id {$message->getUserId()} in RemoveFeedPostFromCacheMessage");
            return;
        }

        foreach ($this->followRepository->getUserFollowers($user->getId(), self::BATCH_SIZE) as $followerIds) {
            $this->feedCacheService->removePostFromCache($followerIds, $message->getPostId());
        }
    }
}