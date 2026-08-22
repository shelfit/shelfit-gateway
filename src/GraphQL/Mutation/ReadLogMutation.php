<?php

namespace App\GraphQL\Mutation;

use App\DTO\BookDto;
use App\DTO\ReadLogDto;
use App\DTO\ReadLogUpdateDto;
use App\Entity\ReadLog;
use App\Exception\UserInputValidationException;
use App\Repository\BookRepository;
use App\Repository\ReadLogPageUpdateRepository;
use App\Repository\ReadLogRepository;
use App\Security\LoggedInUserAwareTrait;
use App\Service\AuthorizationService;
use App\Service\BookService;
use App\Service\ReadLogService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
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
        private ReadLogRepository $readLogRepository,
        private AuthorizationService $authorizationService,
        private ReadLogPageUpdateRepository $readLogPageUpdateRepository,
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
        } catch (UserInputValidationException $e) {
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

    public function createReadLogPageUpdate(Argument $args): ReadLog
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $readLog = $this->readLogRepository->find($args->offsetGet('pageUpdateInput')['logId']);
        if ($readLog === null) {
            throw new UserError('read.log.not.found');
        }

        if (!$this->authorizationService->authorizeResourceOwnership($readLog->getUser()->getId(), $user)) {
            throw new UserError('not.authorized');
        }

        try {
            return $this->readLogService->updateCurrentPage(
                $readLog,
                $args->offsetGet('pageUpdateInput')['toPage']
            );
        } catch (UserInputValidationException) {
            throw new UserError('invalid.to.page');
        }
    }

    public function deleteReadLogPageUpdate(Argument $args): bool
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $pageUpdate = $this->readLogPageUpdateRepository->find($args->offsetGet('pageUpdateId'));
        if ($pageUpdate === null) {
            throw new UserError('page.update.not.found');
        }

        $ownerId = $pageUpdate->getLog()->getUser()->getId();
        if (!$this->authorizationService->authorizeResourceOwnership($ownerId, $user)) {
            throw new UserError('not.authorized');
        }

        $this->readLogService->deletePageUpdate($pageUpdate);
        return true;
    }

    public function updateReadLog(Argument $args): ReadLog
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $readLog = $this->readLogRepository->find($args->offsetGet('updateReadLogInput')['logId']);
        if ($readLog === null) {
            throw new UserError('read.log.not.found');
        }

        if (!$this->authorizationService->authorizeResourceOwnership($readLog->getUser()->getId(), $user)) {
            throw new UserError('not.authorized');
        }

        $readLogUpdateDto = $this->denormalizer->denormalize(
            $args->offsetGet('updateReadLogInput'),
            ReadLogUpdateDto::class
        );

        try {
            return $this->readLogService->updateReadLog($readLog, $readLogUpdateDto);
        } catch (UserInputValidationException $e) {
            throw new UserError($e->getMessage());
        }
    }
}