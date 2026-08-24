<?php

namespace App\DTO;

use App\Entity\Comment;

class CommentThreadDto
{
    public function __construct(
        private ?Comment $root = null,
        /** @var Comment[] $replies */
        private ?array $replies = null,
        private ?bool $hasMoreReplies = null,
    ) {
    }

    public function getRoot(): ?Comment
    {
        return $this->root;
    }

    public function setRoot(?Comment $root): self
    {
        $this->root = $root;
        return $this;
    }

    public function getReplies(): ?array
    {
        return $this->replies;
    }

    public function setReplies(?array $replies): self
    {
        $this->replies = $replies;
        return $this;
    }

    public function getHasMoreReplies(): ?bool
    {
        return $this->hasMoreReplies;
    }

    public function setHasMoreReplies(?bool $hasMoreReplies): self
    {
        $this->hasMoreReplies = $hasMoreReplies;
        return $this;
    }
}