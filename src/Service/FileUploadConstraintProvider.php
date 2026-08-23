<?php

namespace App\Service;

class FileUploadConstraintProvider
{
    public const ALLOWED_CONTENT_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    public const FILE_SIZE_LIMIT = 26214400; // 25MB
}