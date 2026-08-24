<?php

namespace App\DTO;

use App\Entity\Comment;
use App\Entity\FeedPost;
use App\Entity\User;

class CommentDto
{
    public function __construct(
        private ?string $text = null,
        private ?User $user = null,
        private ?FeedPost $feedPost = null,
        private ?Comment $parent = null,
    ) {
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getFeedPost(): ?FeedPost
    {
        return $this->feedPost;
    }

    public function setFeedPost(?FeedPost $feedPost): self
    {
        $this->feedPost = $feedPost;
        return $this;
    }

    public function getParent(): ?Comment
    {
        return $this->parent;
    }

    public function setParent(?Comment $parent): self
    {
        $this->parent = $parent;
        return $this;
    }
}