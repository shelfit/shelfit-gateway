<?php

namespace App\Service;

use App\Entity\Book\Book;
use App\Repository\BookRepository;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class RecommendationService
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
        $response = $this->recommenderClient->request('POST', '/api/recommend', [
            'json' => ['query' => $query]
        ]);

        $ids = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        return $this->bookRepository->getBooksByIds($ids);
    }
}