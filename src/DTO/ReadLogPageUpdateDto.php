<?php

namespace App\DTO;

use App\Entity\ReadLog;
use App\Entity\User;

class ReadLogPageUpdateDto
{
    public function __construct(
       private ?ReadLog $readLog = null,
       private ?int $toPage = null,
       private ?User $user = null,
    ) {
    }

    public function getReadLog(): ?ReadLog
    {
        return $this->readLog;
    }

    public function setReadLog(?ReadLog $readLog): self
    {
        $this->readLog = $readLog;
        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}