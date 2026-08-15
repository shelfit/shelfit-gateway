<?php

namespace App\Message;

class RecalculateBookRatingMessage
{
    public function __construct(
        private int $bookId,
        private int $newRating,
        private ?int $previousRating = null,
    ) {
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function setBookId(int $bookId): self
    {
        $this->bookId = $bookId;
        return $this;
    }

    public function getNewRating(): int
    {
        return $this->newRating;
    }

    public function setNewRating(int $newRating): self
    {
        $this->newRating = $newRating;
        return $this;
    }

    public function getPreviousRating(): ?int
    {
        return $this->previousRating;
    }

    public function setPreviousRating(?int $previousRating): self
    {
        $this->previousRating = $previousRating;
        return $this;
    }
}
