<?php

namespace App\GraphQL\Util;

use App\DTO\Common\PaginationSortDto;
use Overblog\GraphQLBundle\Definition\Argument;

trait PaginatedResolverTrait
{
    public static function paginationSortDtoFromArgs(Argument $args, string $defaultSortField): PaginationSortDto
    {
        return (new PaginationSortDto())
            ->setLimit((int)($args->offsetGet('limit') ?? PaginationSortDto::DEFAULT_LIMIT))
            ->setOffset((int)($args->offsetGet('offset') ?? PaginationSortDto::DEFAULT_OFFSET))
            ->setSortField($args->offsetGet('sortField') ?? $defaultSortField)
            ->setSortDirection($args->offsetGet('sortDirection') ?? 'asc');
    }
}