<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Index(name: 'IDX_PATH', columns: ['path'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?FeedPost $feedPost = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $depth = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $numLikes = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $numReplies = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $parent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getFeedPost(): ?FeedPost
    {
        return $this->feedPost;
    }

    public function setFeedPost(?FeedPost $feedPost): static
    {
        $this->feedPost = $feedPost;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): static
    {
        $this->depth = $depth;

        return $this;
    }

    public function getNumLikes(): int
    {
        return $this->numLikes;
    }

    public function setNumLikes(int $numLikes): static
    {
        $this->numLikes = $numLikes;

        return $this;
    }

    public function getNumReplies(): int
    {
        return $this->numReplies;
    }

    public function setNumReplies(int $numReplies): static
    {
        $this->numReplies = $numReplies;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }
}
