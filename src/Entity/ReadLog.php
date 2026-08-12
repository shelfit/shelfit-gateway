<?php

namespace App\Entity;

use App\Entity\Book\Book;
use App\Repository\ReadLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReadLogRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_BOOK_USER', columns: ['book_id', 'user_id'])]
class ReadLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'book_id', nullable: false)]
    private ?Book $book = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $currentPage = null;

    #[ORM\Column(nullable: true)]
    private ?float $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $review = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    /**
     * @var Collection<int, ReadLogPageUpdate>
     */
    #[ORM\OneToMany(targetEntity: ReadLogPageUpdate::class, mappedBy: 'log', orphanRemoval: true)]
    private Collection $readLogPageUpdates;

    public function __construct()
    {
        $this->readLogPageUpdates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

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

    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): static
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(?string $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * @return Collection<int, ReadLogPageUpdate>
     */
    public function getReadLogPageUpdates(): Collection
    {
        return $this->readLogPageUpdates;
    }

    public function addReadLogPageUpdate(ReadLogPageUpdate $readLogPageUpdate): static
    {
        if (!$this->readLogPageUpdates->contains($readLogPageUpdate)) {
            $this->readLogPageUpdates->add($readLogPageUpdate);
            $readLogPageUpdate->setLog($this);
        }

        return $this;
    }

    public function removeReadLogPageUpdate(ReadLogPageUpdate $readLogPageUpdate): static
    {
        if ($this->readLogPageUpdates->removeElement($readLogPageUpdate)) {
            // set the owning side to null (unless already changed)
            if ($readLogPageUpdate->getLog() === $this) {
                $readLogPageUpdate->setLog(null);
            }
        }

        return $this;
    }
}
