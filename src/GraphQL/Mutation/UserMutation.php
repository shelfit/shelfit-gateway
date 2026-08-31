<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use App\Security\LoggedInUserAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class UserMutation implements MutationInterface {
    use LoggedInUserAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    public function editAccount(Argument $args): User
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $input = $args->offsetGet('editAccountInput');
        $user->setUsername($input['username']);

        if (!empty($input['bio'])) {
            $user->setBio($input['bio']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
}