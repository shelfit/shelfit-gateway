<?php

namespace App\DTO;

use App\Entity\Book\BookVisibility;
use Symfony\Component\Validator\Constraints as Assert;

class BookDto
{
    public function __construct(
        private ?string $title = null,
        private ?string $author = null,
        private ?array $genres = null,

        #[Assert\Positive]
        private ?int $pageCount = null,

        #[Assert\Url(protocols: ['https'])]
        private ?string $coverUrl = null,

        private ?string $description = null,

        #[Assert\Choice(options: BookVisibility::ALL_VISIBILITIES)]
        private ?string $visibility = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function setGenres(?array $genres): self
    {
        $this->genres = $genres;
        return $this;
    }

    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    public function setPageCount(?int $pageCount): self
    {
        $this->pageCount = $pageCount;
        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(?string $coverUrl): self
    {
        $this->coverUrl = $coverUrl;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(?string $visibility): self
    {
        $this->visibility = $visibility;
        return $this;
    }
}
