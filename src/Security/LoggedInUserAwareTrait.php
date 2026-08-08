<?php

namespace App\Security;

use App\Entity\User;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

trait LoggedInUserAwareTrait
{
    private const NOT_AUTHENTICATED = 'not.authenticated';

    public static function getLoggedInUser(Security $security): User
    {
        $user = $security->getUser();

        if ($user === null) {
            throw new AuthenticationException('User not logged in');
        }

        if (!$user instanceof User) {
            throw new RuntimeException('Logged in user isn\'t an instance of ' . User::class);
        }

        return $user;
    }
}