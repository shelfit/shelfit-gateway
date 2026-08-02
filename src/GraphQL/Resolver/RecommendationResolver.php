<?php

namespace App\GraphQL\Resolver;

use App\Service\RecommendationService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class RecommendationResolver implements QueryInterface
{
    public function __construct(
        private RecommendationService $recommendationService,
        private LoggerInterface $logger,
    ) {
    }

    public function recommendBooks(Argument $args): array
    {
        try {
            $query = $args->offsetGet("query");
            return $this->recommendationService->getRecommendations($query);
        }
        catch (Throwable $t) {
            $this->logger->error("Can't get recommendation: {$t->getMessage()}", ['exception' => $t]);
            throw new UserError("recommendation.error");
        }
    }
}