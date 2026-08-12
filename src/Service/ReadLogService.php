<?php

namespace App\Service;

use App\DTO\ReadLogDto;
use App\DTO\ReadLogPageUpdateDto;
use App\Entity\Book\BookStatus;
use App\Entity\ReadLog;
use App\Entity\ReadLogPageUpdate;
use App\Exception\AuthorizationException;
use App\Exception\UserInputValidationException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class ReadLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuthorizationService $authorizationService,
    ) {
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
     * @throws AuthorizationException
     * @throws UserInputValidationException
     */
    public function updateCurrentPage(ReadLogPageUpdateDto $readLogPageUpdateDto): ReadLog
    {
        if (!$this->authorizationService->authorizeReadLogPageUpdate(
            $readLogPageUpdateDto->getReadLog(),
            $readLogPageUpdateDto->getUser())
        )  {
            throw new AuthorizationException();
        }

        $readLog = $readLogPageUpdateDto->getReadLog();
        $book = $readLog->getBook();
        if (
            $readLogPageUpdateDto->getToPage() <= $readLog->getCurrentPage() ||
            $readLogPageUpdateDto->getToPage() > $book->getPageCount()
        ) {
            throw new UserInputValidationException();
        }

        $this->entityManager->beginTransaction();

        $readLogPageUpdate = (new ReadLogPageUpdate())
            ->setLog($readLog)
            ->setFromPage($readLog->getCurrentPage())
            ->setToPage($readLogPageUpdateDto->getToPage())
            ->setCreatedAt(new DateTimeImmutable());

        $readLog
            ->setCurrentPage($readLogPageUpdateDto->getToPage())
            ->setUpdatedAt(new DateTimeImmutable());

        if ($readLogPageUpdateDto->getToPage() === $book->getPageCount()) {
            $readLog
                ->setStatus(BookStatus::STATUS_FINISHED)
                ->setFinishedAt(new DateTimeImmutable());
        }

        $this->entityManager->persist($readLogPageUpdate);
        $this->entityManager->persist($readLog);
        $this->entityManager->flush();

        $this->entityManager->commit();

        return $readLog;
    }
}