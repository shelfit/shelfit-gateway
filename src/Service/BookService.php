<?php

namespace App\Service;

use App\DTO\BookDto;
use App\DTO\Common\PaginationSortDto;
use App\Entity\Book\Book;
use App\Entity\Book\BookSource;
use App\Exception\UserInputValidationException;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

readonly class BookService
{
    private const DEFAULT_COVER_URL = "/img/cover";

    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private QdrantService $qdrantService,
        private BookRepository $bookRepository,
    ) {
    }

    /**
     * @throws UserInputValidationException
     */
    public function createBook(BookDto $bookDto): Book
    {
        $violations = $this->validator->validate($bookDto);
        if (count($violations) > 0) {
            throw new UserInputValidationException(self::formatValidationErrorMessages($violations));
        }

        $book = (new Book())
            ->setTitle($bookDto->getTitle())
            ->setAuthor($bookDto->getAuthor())
            ->setGenres($bookDto->getGenres())
            ->setPageCount($bookDto->getPageCount())
            ->setCoverUrl($bookDto->getCoverUrl() ?? self::DEFAULT_COVER_URL)
            ->setDescription($bookDto->getDescription())
            ->setSource(BookSource::SOURCE_USER)
            ->setVisibility($bookDto->getVisibility())
            ->setRating(0)
            ->setNumRatings(0);

        $this->entityManager->persist($book);
        $this->entityManager->flush();
        return $book;
    }

    public static function formatValidationErrorMessages(ConstraintViolationListInterface $violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = match ($violation->getPropertyPath()) {
                'pageCount' => 'invalid.page_count',
                'coverUrl' => 'invalid.cover.url',
                'visibility' => 'invalid.visibility',
            };
        }
        return implode("\n", $messages);
    }

    /**
     * @return Book[]
     */
    public function searchBooks(string $query, PaginationSortDto $paginationSortDto): array
    {
        try {
            $recommenderBooks = $this->qdrantService->searchBooks(
                $query,
                $paginationSortDto->getLimit(),
                $paginationSortDto->getOffset()
            );
        } catch (Throwable) {
            $recommenderBooks = [];
        }

        $repositoryBooks = $this->bookRepository->searchBooks($query, $paginationSortDto);

        $limitSplit = ceil($paginationSortDto->getLimit() / 2);

        if (empty($recommenderBooks) || empty($repositoryBooks)) {
            $booksMerged = array_merge($repositoryBooks, $recommenderBooks);
        } else {
            $booksMerged = array_merge(
                array_slice($repositoryBooks, 0, $limitSplit),
                array_slice($recommenderBooks, 0, $limitSplit)
            );
        }
        return array_slice($booksMerged, 0, $paginationSortDto->getLimit());
    }
}