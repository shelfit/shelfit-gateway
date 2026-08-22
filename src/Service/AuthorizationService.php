<?php

namespace App\Service;

use App\Entity\FeedPostLike;
use App\Entity\ReadLog;
use App\Entity\User;

readonly class AuthorizationService
{
    public function authorizeReadLogActions(ReadLog $log, User $user): bool
    {
        return $log->getUser()->getId() === $user->getId();
    }

    public function authorizeUnlikeFeedPost(FeedPostLike $like, User $user): bool
    {
        return $like->getUser()->getId() === $user->getId();
    }
}