<?php

namespace App\DTO\Common;

class PaginationSortDto
{
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_OFFSET = 0;
    public const DEFAULT_SORT_FIELD = 'createdAt';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(
        private ?int $limit = null,
        private ?int $offset = null,
        private ?string $sortField = null,
        private ?string $sortDirection = null,
    ) {
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(?int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function getSortField(): ?string
    {
        return $this->sortField;
    }

    public function setSortField(?string $sortField): self
    {
        $this->sortField = $sortField;
        return $this;
    }

    public function getSortDirection(): ?string
    {
        return $this->sortDirection;
    }

    public function setSortDirection(?string $sortDirection): self
    {
        $this->sortDirection = $sortDirection;
        return $this;
    }
}