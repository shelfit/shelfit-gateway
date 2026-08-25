<?php

namespace App\Service;

use App\DTO\Common\PaginationSortDto;
use App\DTO\ReadLogDto;
use App\DTO\ReadLogUpdateDto;
use App\Entity\Book\BookVisibility;
use App\Entity\FeedPostType;
use App\Entity\ReadLog;
use App\Entity\ReadLogPageUpdate;
use App\Entity\ReadLogStatus;
use App\Entity\User;
use App\Exception\UserInputValidationException;
use App\Message\CacheFeedPostMessage;
use App\Message\RecalculateBookRatingMessage;
use App\Repository\ReadLogPageUpdateRepository;
use App\Repository\ReadLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ReadLogService
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private MessageBusInterface         $bus,
        private ValidatorInterface          $validator,
        private ReadLogPageUpdateRepository $readLogPageUpdateRepository,
        private FeedService                 $feedPostService,
        private ReadLogRepository           $readLogRepository,
    ) {
    }

    /**
     * @param string[] $statuses
     * @param string[] $allowedVisibilities
     * @return ReadLog[]
     */
    public function getUserReadLogs(
        User $user,
        array $statuses,
        array $allowedVisibilities,
        PaginationSortDto $paginationSortDto
    ): array
    {
        $logs = $this->readLogRepository->getUserReadLogs($user, $statuses, $allowedVisibilities, $paginationSortDto);

        $resultsByStatus = array_combine($statuses, array_fill(0, count($statuses), []));
        foreach ($logs as $log) {
            $resultsByStatus[$log->getStatus()][] = $log;
        }

        return array_merge(...array_values($resultsByStatus));
    }

    public function createReadLog(ReadLogDto $readLogDto): ReadLog
    {
        $currentPage = self::parseInputPage(
            $readLogDto->getCurrentPage(),
            $readLogDto->getBook()->getPageCount()
        );

        $readLog = (new ReadLog())
            ->setBook($readLogDto->getBook())
            ->setUser($readLogDto->getUser())
            ->setCurrentPage($currentPage)
            ->setStatus($readLogDto->getStatus())
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($readLog);
        $this->entityManager->flush();
        return $readLog;
    }

    private static function parseInputPage(?int $page, int $bookPageCount): int
    {
        if ($page === null || $page < 0) {
            return 0;
        }

        if ($page > $bookPageCount) {
            return $bookPageCount;
        }

        return $page;
    }

    /**
     * @throws UserInputValidationException
     */
    public function updateCurrentPage(ReadLog $readLog, int $toPage): ReadLog
    {
        $feedPost = null;

        $book = $readLog->getBook();
        if ($toPage <= $readLog->getCurrentPage() || $toPage > $book->getPageCount()) {
            throw new UserInputValidationException();
        }

        $this->entityManager->beginTransaction();

        $readLogPageUpdate = (new ReadLogPageUpdate())
            ->setLog($readLog)
            ->setFromPage($readLog->getCurrentPage())
            ->setToPage($toPage)
            ->setCreatedAt(new DateTimeImmutable());

        $readLog
            ->setCurrentPage($toPage)
            ->setUpdatedAt(new DateTimeImmutable());

        if ($toPage === $book->getPageCount()) {
            $readLog
                ->setStatus(ReadLogStatus::STATUS_FINISHED)
                ->setFinishedAt(new DateTimeImmutable());

            if ($book->getVisibility() === BookVisibility::VISIBILITY_PUBLIC) {
                $feedPost = $this->feedPostService->createPostFromReadLog($readLog, $readLog->getUser(), FeedPostType::TYPE_FINISHED);
            }
        }

        $this->entityManager->persist($readLogPageUpdate);
        $this->entityManager->persist($readLog);
        $this->entityManager->flush();

        $this->entityManager->commit();

        if ($feedPost !== null) {
            $this->bus->dispatch(new CacheFeedPostMessage($feedPost->getId()));
        }

        return $readLog;
    }

    public function deletePageUpdate(ReadLogPageUpdate $pageUpdate): void
    {
        $readLog = $pageUpdate->getLog();
        $lastUpdate = $this->readLogPageUpdateRepository->getLastPageUpdateForLog($readLog);

        $this->entityManager->beginTransaction();

        if ($lastUpdate !== null && $lastUpdate->getId() === $pageUpdate->getId()) {
            $readLog->setCurrentPage($pageUpdate->getFromPage());

            if ($pageUpdate->getToPage() === $readLog->getBook()->getPageCount() &&
                $readLog->getStatus() === ReadLogStatus::STATUS_FINISHED) {
                $readLog->setStatus(ReadLogStatus::STATUS_READING);
            }
        }

        $readLog->setUpdatedAt(new DateTimeImmutable());

        $this->entityManager->persist($readLog);
        $this->entityManager->remove($pageUpdate);
        $this->entityManager->flush();

        $this->entityManager->commit();
    }

    /**
     * @throws UserInputValidationException
     * @throws ExceptionInterface
     */
    public function updateReadLog(ReadLog $readLog, ReadLogUpdateDto $updateDto): ReadLog
    {
        $violations = $this->validator->validate($updateDto);
        if (count($violations) > 0) {
            throw new UserInputValidationException('invalid.rating');
        }

        if (
            $updateDto->getRating() === null &&
            $updateDto->getReview() === null &&
            $updateDto->getStatus() === null
        ) {
            return $readLog;
        }

        $previousRating = $readLog->getRating();
        $previousReview = $readLog->getReview();

        if ($updateDto->getRating() !== null) {
            $readLog->setRating($updateDto->getRating());
        }

        if ($updateDto->getReview() !== null) {
            $readLog->setReview($updateDto->getReview());
        }

        if ($updateDto->getStatus() !== null) {
            $readLog->setStatus($updateDto->getStatus());

            if ($updateDto->getStatus() === ReadLogStatus::STATUS_FINISHED) {
                $readLog->setFinishedAt(new DateTimeImmutable());
            }
        }

        $readLog->setUpdatedAt(new DateTimeImmutable());

        $this->entityManager->persist($readLog);
        $this->entityManager->flush();

        if ($updateDto->getRating() !== null && $updateDto->getRating() !== $previousRating) {
            $this->bus->dispatch(new RecalculateBookRatingMessage(
                $readLog->getBook()->getId(),
                $updateDto->getRating(),
                $previousRating
            ));
        }

        if (
            $readLog->getBook()->getVisibility() === BookVisibility::VISIBILITY_PUBLIC &&
            (
                ($updateDto->getRating() !== null && $previousRating === null) ||
                ($updateDto->getReview() !== null && $previousReview === null)
            )
        ) {
            $feedPost = $this->feedPostService->createPostFromReadLog($readLog, $readLog->getUser(), FeedPostType::TYPE_REVIEW);
            $this->bus->dispatch(new CacheFeedPostMessage($feedPost->getId()));
        }

        return $readLog;
    }
}