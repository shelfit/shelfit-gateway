<?php

namespace App\MessageHandler;

use App\Message\RecalculateBookRatingMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class RecalculateBookRatingMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RecalculateBookRatingMessage $message): void
    {
        $params = [
            'id' => $message->getBookId(),
            'new_rating' => $message->getNewRating(),
        ];

        if ($message->getPreviousRating() === null) {
            $sql = "
                UPDATE books SET
                    rating = ((coalesce(rating, 0) * coalesce(num_ratings, 0)) + :new_rating) / (coalesce(num_ratings, 0) + 1),
                    num_ratings = coalesce(num_ratings, 0) + 1
                WHERE id = :id
            ";
        } else {
            $sql = "
                UPDATE books SET
                    rating = ((coalesce(rating, 0) * coalesce(num_ratings, 0) - :prev_rating) + :new_rating) / coalesce(num_ratings, 1)
                WHERE id = :id
            ";

            $params['prev_rating'] = $message->getPreviousRating();
        }

        $conn = $this->entityManager->getConnection();
        $updatedRowCount = $conn->executeStatement($sql, $params);

        if ($updatedRowCount === 0) {
            $this->logger->error("Recalculate rating failed - 0 rows affected for book id {$message->getBookId()}");
        }
    }
}