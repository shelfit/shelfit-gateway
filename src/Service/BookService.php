<?php

namespace App\Service;

use App\DTO\BookDto;
use App\Entity\Book\Book;
use App\Entity\Book\BookSource;
use App\Exception\UserInputValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class BookService
{
    private const DEFAULT_COVER_URL = "/img/cover";

    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
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
}