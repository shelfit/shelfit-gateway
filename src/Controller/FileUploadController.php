<?php

namespace App\Controller;

use App\DTO\FileUploadDto;
use App\Entity\User;
use App\Exception\FileUploadValidationException;
use App\Service\AccountService;
use App\Service\FileUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class FileUploadController extends AbstractController
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly AccountService $accountService,
    ) {
    }

    #[Route('/api/accounts/photo-url', methods: ['POST'])]
    public function getProfilePicUploadPresignedUrl(
        #[MapRequestPayload] FileUploadDto $fileUploadDto,
        #[CurrentUser] User $user,
    ): JsonResponse
    {
        $uploadData = $this->fileUploadService->getUploadPresignedUrl($fileUploadDto, $user);

        return $this->json([
            'status' => 'success',
            'key' => $uploadData['key'],
            'url' => $uploadData['url']
        ]);
    }

    #[Route('/api/accounts/photo', methods: ['POST'])]
    public function notifyUpload(Request $req, #[CurrentUser] User $user): JsonResponse
    {
        $key = $req->toArray()['key'] ?? null;
        if ($key === null) {
            return $this->json([
                'status' => 'error',
                'message' => 'No upload key provided'
            ], 400);
        }

        try {
            $imgUrl = $this->fileUploadService->verifyUpload($key, $user);
        } catch (FileUploadValidationException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }

        $this->accountService->updateProfilePictureKey($user, $key);

        return $this->json([
            'status' => 'success',
            'url' => $imgUrl
        ]);
    }
}