<?php

namespace App\Service;

use App\DTO\ReadLogDto;
use App\Entity\ReadLog;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class ReadLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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
}