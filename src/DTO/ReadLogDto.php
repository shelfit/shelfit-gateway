<?php

namespace App\DTO;

use App\Entity\Book\Book;
use App\Entity\User;

class ReadLogDto
{
    public function __construct(
        private ?Book $book = null,
        private ?User $user = null,
        private ?int $currentPage = null,
        private ?string $status = null,
    ) {
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;
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

    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(?int $currentPage): self
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }
}