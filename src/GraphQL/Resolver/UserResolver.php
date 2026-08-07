<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LoggedInUserAwareTrait;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class UserResolver implements QueryInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
    ) {
    }

    public function me(): User
    {
        try {
            return self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError("not.authenticated");
        }
    }
}