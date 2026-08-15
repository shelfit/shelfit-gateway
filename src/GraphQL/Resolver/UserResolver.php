<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\UserRepository;
use App\Security\LoggedInUserAwareTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class UserResolver implements QueryInterface
{
    use LoggedInUserAwareTrait, PaginatedResolverTrait;

    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
    ) {
    }

    public function user(Argument $args): User
    {
        $user = $this->userRepository->find($args->offsetGet('id'));

        if ($user === null) {
            throw new UserError('no.user');
        }

        return $user;
    }

    public function me(): User
    {
        try {
            return self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }
    }

    /**
     * @return User[]
     */
    public function searchUsers(Argument $args): array
    {
        $query = $args->offsetGet('query');
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'createdAt');
        return $this->userRepository->searchUsers($query, $paginationSortDto);
    }
}