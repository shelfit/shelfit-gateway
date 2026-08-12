<?php

namespace App\Entity;

use App\Repository\ReadLogPageUpdateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReadLogPageUpdateRepository::class)]
class ReadLogPageUpdate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'readLogPageUpdates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReadLog $log = null;

    #[ORM\Column]
    private ?int $fromPage = null;

    #[ORM\Column]
    private ?int $toPage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLog(): ?ReadLog
    {
        return $this->log;
    }

    public function setLog(?ReadLog $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getFromPage(): ?int
    {
        return $this->fromPage;
    }

    public function setFromPage(int $fromPage): static
    {
        $this->fromPage = $fromPage;

        return $this;
    }

    public function getToPage(): ?int
    {
        return $this->toPage;
    }

    public function setToPage(int $toPage): static
    {
        $this->toPage = $toPage;

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
}
