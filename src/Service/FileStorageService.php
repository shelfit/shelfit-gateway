<?php

namespace App\Service;

use App\Entity\User;
use Aws\S3\S3Client;

readonly class FileStorageService
{
    public function __construct(
        private string $profilePicturesBucket,
        private string $s3WebServeBaseUri,
        private string $defaultProfilePictureUrl,
        private S3Client $s3Client,
    ) {
    }

    public function resolveUserProfilePictureUrl(User $user): string
    {
        $key = $user->getProfilePictureKey();

        if ($key === null) {
            return $this->defaultProfilePictureUrl;
        }

        return $this->s3WebServeBaseUri . '/' . $key;
    }

    public function deleteFile(string $key): void
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->profilePicturesBucket,
            'Key' => $key,
        ]);
    }
}