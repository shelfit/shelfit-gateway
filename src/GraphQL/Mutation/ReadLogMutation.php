<?php

namespace App\GraphQL\Mutation;

use App\DTO\BookDto;
use App\DTO\ReadLogDto;
use App\Entity\ReadLog;
use App\Repository\BookRepository;
use App\Security\LoggedInUserAwareTrait;
use App\Service\BookService;
use App\Service\ReadLogService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class ReadLogMutation implements MutationInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private BookRepository $bookRepository,
        private Security $security,
        private ReadLogService $readLogService,
        private DenormalizerInterface $denormalizer,
        private BookService $bookService,
    ) {
    }

    public function createReadLogFromExistingBook(Argument $args): ReadLog
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $book = $this->bookRepository->find($args->offsetGet('bookId'));

        if ($book === null) {
            throw new UserError('no.book');
        }

        $readLogDto = $this->denormalizer->denormalize($args->offsetGet('readLogInput'), ReadLogDto::class);
        $readLogDto->setBook($book)->setUser($user);

        try {
            return $this->readLogService->createReadLog($readLogDto);
        } catch (UniqueConstraintViolationException) {
            throw new UserError('read.log.already.exists');
        }
    }

    public function createReadLogFromNewBook(Argument $args): ReadLog
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $bookInput = $args->offsetGet('book');
        $bookDto = $this->denormalizer->denormalize($bookInput, BookDto::class);

        try {
            $book = $this->bookService->createBook($bookDto);
        } catch (RuntimeException $e) {
            throw new UserError($e->getMessage());
        }

        $readLogDto = $this->denormalizer->denormalize($args->offsetGet('readLogInput'), ReadLogDto::class);
        $readLogDto->setBook($book)->setUser($user);

        try {
            return $this->readLogService->createReadLog($readLogDto);
        } catch (UniqueConstraintViolationException) {
            throw new UserError('read.log.already.exists');
        }
    }
}