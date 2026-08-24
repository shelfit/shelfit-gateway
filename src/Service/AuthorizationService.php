<?php

namespace App\Service;

use App\Entity\User;

readonly class AuthorizationService
{
    public function authorizeResourceOwnership(int $resourceOwnerId, User $user): bool
    {
        return $resourceOwnerId === $user->getId();
    }
}
