<?php

namespace App\Message;

readonly class RemoveFeedPostFromCacheMessage
{
    public function __construct(
        private int $postId,
        private int $userId,
    ) {
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}