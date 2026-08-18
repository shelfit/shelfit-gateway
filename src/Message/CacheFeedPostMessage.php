<?php

namespace App\Message;

class CacheFeedPostMessage
{
    public function __construct(
        private int $feedPostId,
    ) {
    }

    public function getFeedPostId(): int
    {
        return $this->feedPostId;
    }

    public function setFeedPostId(int $feedPostId): self
    {
        $this->feedPostId = $feedPostId;
        return $this;
    }
}