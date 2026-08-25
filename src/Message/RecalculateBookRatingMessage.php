<?php

namespace App\Message;

class RecalculateBookRatingMessage
{
    public function __construct(
        private int $bookId,
        private float $newRating,
        private ?float $previousRating = null,
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

    public function getNewRating(): float
    {
        return $this->newRating;
    }

    public function setNewRating(float $newRating): self
    {
        $this->newRating = $newRating;
        return $this;
    }

    public function getPreviousRating(): ?float
    {
        return $this->previousRating;
    }

    public function setPreviousRating(?float $previousRating): self
    {
        $this->previousRating = $previousRating;
        return $this;
    }
}
