<?php

namespace App\GraphQL\Resolver;

use App\Entity\Book\Book;
use App\Entity\Book\BookVisibility;
use App\GraphQL\Util\PaginatedResolverTrait;
use App\Repository\BookRepository;
use App\Service\BookService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class BookResolver implements QueryInterface
{
    use PaginatedResolverTrait;

    public function __construct(
        private BookRepository $bookRepository,
        private BookService $bookService,
    ) {}

    public function resolveBook(Argument $args): ?Book
    {
        $id = $args->offsetGet("id");
        return $this->bookRepository->findOneBy(["id" => $id, "visibility" => BookVisibility::VISIBILITY_PUBLIC]);
    }

    /**
     * @return Book[]
     */
    public function searchBooks(Argument $args): array
    {
        $query = $args->offsetGet('query');
        $paginationSortDto = self::paginationSortDtoFromArgs($args, 'num_ratings', 'desc');

        return $this->bookService->searchBooks($query, $paginationSortDto);
    }
}