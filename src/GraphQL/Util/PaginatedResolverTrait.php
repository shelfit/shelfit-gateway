<?php

namespace App\GraphQL\Util;

use App\DTO\Common\PaginationSortDto;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;

trait PaginatedResolverTrait
{
    /**
     * @throws UserError
     */
    public static function paginationSortDtoFromArgs(Argument $args, string $defaultSortField, string $sortDirection = 'asc'): PaginationSortDto
    {
        $limit = (int)($args->offsetGet('limit') ?? PaginationSortDto::DEFAULT_LIMIT);
        $offset = (int)($args->offsetGet('offset') ?? PaginationSortDto::DEFAULT_OFFSET);

        if ($limit < 0 || $limit > 1000) {
            throw new UserError('invalid.limit');
        }

        if ($offset < 0) {
            throw new UserError('invalid.offset');
        }

        return (new PaginationSortDto())
            ->setLimit($limit)
            ->setOffset($offset)
            ->setSortField($args->offsetGet('sortField') ?? $defaultSortField)
            ->setSortDirection($args->offsetGet('sortDirection') ?? $sortDirection);
    }
}