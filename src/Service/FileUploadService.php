<?php

namespace App\Service;

use App\DTO\FileUploadDto;
use App\Entity\User;
use App\Exception\FileUploadValidationException;
use App\Exception\UserInputValidationException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Symfony\Component\Uid\Factory\UlidFactory;

readonly class FileUploadService
{
    private const PRESIGNED_URL_TTL = '+5 minutes';

    public function __construct(
        private string $profilePicturesBucket,
        private string $s3WebServeBaseUri,
        private S3Client $publicS3Client,
        private S3Client $s3Client,
        private UlidFactory $ulidFactory,
    ) {
    }

    /**
     * @return array{key: string, url: string}
     */
    public function getUploadPresignedUrl(FileUploadDto $fileUploadDto, User $user): array
    {
        $key = $this->generateObjectKey($fileUploadDto, $user->getId());

        $command = $this->publicS3Client->getCommand('PutObject', [
            'Bucket' => $this->profilePicturesBucket,
            'Key' => $key,
            'ContentType' => $fileUploadDto->getContentType(),
        ]);

        $request = $this->publicS3Client->createPresignedRequest($command, self::PRESIGNED_URL_TTL);

        return [
            'key' => $key,
            'url' => (string)$request->getUri(),
        ];
    }

    private function generateObjectKey(FileUploadDto $fileUploadDto, int $userId): string
    {
        return "usr-" . $userId . "-" . $this->ulidFactory->create() . "." . $fileUploadDto->getExtension();
    }

    /**
     * @throws FileUploadValidationException
     */
    public function verifyUpload(string $key, User $user): string
    {
        if ($user->getProfilePictureKey() !== null && $user->getProfilePictureKey() === $key) {
            return $this->s3WebServeBaseUri . '/' . $key;
        }

        if (!str_starts_with($key, "usr-{$user->getId()}-")) {
            throw new FileUploadValidationException("Verifying unowned file");
        }

        try {
            $objMeta = $this->s3Client->headObject([
                'Bucket' => $this->profilePicturesBucket,
                'Key' => $key,
            ]);
        } catch (S3Exception) {
            throw new FileUploadValidationException("Object with key {$key} not found");
        }

        if (
            $objMeta['ContentLength'] > FileUploadConstraintProvider::FILE_SIZE_LIMIT ||
            !in_array($objMeta['ContentType'], FileUploadConstraintProvider::ALLOWED_CONTENT_TYPES, true)
        ) {
            $this->deleteFile($key);
            throw new FileUploadValidationException("Invalid file size or format");
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