<?php

namespace App\Service;

use App\Entity\User;

readonly class AuthorizationService
{
    public function authorizeResourceOwnership(int $resourceId, User $user): bool
    {
        return $resourceId === $user->getId();
    }
}
