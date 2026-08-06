<?php

namespace App\GraphQL\Resolver;

use App\Entity\Book\Book;
use App\Repository\BookRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

readonly class BookResolver implements QueryInterface
{
    public function __construct(
        private BookRepository $bookRepository,
    ) {}

    public function resolveBook(Argument $args): ?Book
    {
        $id = $args->offsetGet("id");
        return $this->bookRepository->findOneBy(["id" => $id]);
    }
}