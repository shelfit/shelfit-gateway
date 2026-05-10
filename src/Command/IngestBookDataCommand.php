<?php

namespace App\Command;

use App\Entity\Book\BookSource;
use App\Entity\Book\BookVisibility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;

#[AsCommand(name: 'hermes:ingest-books')]
class IngestBookDataCommand
{
    private const CHUNK_SIZE = 500;

    public function __construct(
        #[Autowire('%kernel.project_dir%/var/data/books.csv')]
        private readonly string $sourcePath,
        private readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Starting book data ingest...");

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $data = $serializer->decode(file_get_contents($this->sourcePath), 'csv');

        $io->info("Data loaded...");
        $io->progressStart(count($data));

        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        try {
            foreach (array_chunk($data, self::CHUNK_SIZE) as $chunk) {
                $sql = "
                    INSERT INTO books (title, author, genres, page_count, cover_url, description, source, visibility)
                    VALUES " .
                    implode(
                        ", ",
                        array_fill(0, count($chunk), "(?, ?, ?, ?, ?, ?, ?, ?)")
                    );

                $params = [];
                foreach ($chunk as $book) {
                    /**
                     * Some entries list the author, translator, illustrator... in the author field
                     * and some entries have (Goodreads author) next to the author's name
                     */
                    $creditList = explode(",", $book['author']);
                    $author = trim($creditList[0]);

                    if (preg_match('/\([^)]*\)/', $author)) {
                        $author = trim(substr($author, 0, strpos($author, "(")));
                    }

                    /**
                     * Most of the books have many genres, and the last few are ultra specific
                     */
                    $genres = $book['genres'] ?? [];
                    $genres = array_slice(
                        json_decode($genres, true, 512, JSON_THROW_ON_ERROR),
                        0,
                        5
                    );

                    $params[] = $book['title'];
                    $params[] = $author;
                    $params[] = json_encode($genres);
                    $params[] = (int) $book['pages'];
                    $params[] = $book['coverImg'];
                    $params[] = $book['description'];
                    $params[] = BookSource::SOURCE_SYSTEM;
                    $params[] = BookVisibility::VISIBILITY_PUBLIC;
                }

                $conn->executeStatement($sql, $params);
                $io->progressAdvance(count($chunk));
            }

            $conn->commit();
        }
        catch (Throwable $e) {
            $conn->rollBack();
            $io->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        $io->progressFinish();
        $io->success(count($data) . " books inserted successfully!");
        return Command::SUCCESS;
    }
}
