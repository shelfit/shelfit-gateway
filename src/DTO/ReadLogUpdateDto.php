<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReadLogUpdateDto
{
    public function __construct(
        #[Assert\Range(
            notInRangeMessage: 'Rating must be between 1 and 5',
            min: 1,
            max: 5
        )]
        private ?float $rating = null,
        private ?string $review = null,
        private ?string $status = null,
    ) {
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(?string $review): self
    {
        $this->review = $review;
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