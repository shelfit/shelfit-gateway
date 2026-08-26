<?php

namespace App\DTO;

use DateTimeImmutable;

class ReadLogPageUpdateDto
{
    public function __construct(
        private ?int $toPage = null,
        private ?DateTimeImmutable $pageUpdateDateTime = null,
    ) {
    }

    public function getToPage(): ?int
    {
        return $this->toPage;
    }

    public function setToPage(?int $toPage): self
    {
        $this->toPage = $toPage;
        return $this;
    }

    public function getPageUpdateDateTime(): ?DateTimeImmutable
    {
        return $this->pageUpdateDateTime;
    }

    public function setPageUpdateDateTime(?DateTimeImmutable $pageUpdateDateTime): self
    {
        $this->pageUpdateDateTime = $pageUpdateDateTime;
        return $this;
    }
}