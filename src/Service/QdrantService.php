<?php

namespace App\Service;

use App\Entity\Book\Book;
use App\Repository\BookRepository;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class QdrantService
{
    public function __construct(
        private HttpClientInterface $recommenderClient,
        private BookRepository $bookRepository,
    ) {
    }

    /**
     * @returns Book[]
     * @throws \JsonException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getRecommendations(string $query): array
    {
        return $this->makeRecommenderRequest(['query' => $query], '/api/recommend');
    }

    /**
     * @return Book[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    public function searchBooks(string $query, int $limit, int $offset): array
    {
        return $this->makeRecommenderRequest(
            [
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset
            ],
            '/api/search'
        );
    }

    /**
     * @return Book[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
     */
    private function makeRecommenderRequest(array $payload, string $endpoint): array
    {
        $response = $this->recommenderClient->request('POST', $endpoint, ['json' => $payload]);
        return $this->bookRepository->getBooksByIds(
            json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }
}