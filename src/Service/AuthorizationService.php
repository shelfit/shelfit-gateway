<?php

namespace App\Service;

use App\Entity\ReadLog;
use App\Entity\User;

readonly class AuthorizationService
{
    public function authorizeReadLogPageUpdate(ReadLog $log, User $user): bool
    {
        return $log->getUser()->getId() === $user->getId();
    }
}