<?php

namespace App\DTO;

use App\Service\FileUploadConstraintProvider;
use Symfony\Component\Validator\Constraints as Assert;

class FileUploadDto
{
    private const CONTENT_TYPE_TO_EXTENSION_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        #[Assert\Choice(
            choices: FileUploadConstraintProvider::ALLOWED_CONTENT_TYPES,
            message: 'Unsupported image format'
        )]
        private ?string $contentType = null,
    ) {
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getExtension(): ?string
    {
        return self::CONTENT_TYPE_TO_EXTENSION_MAP[$this->contentType] ?? null;
    }
}