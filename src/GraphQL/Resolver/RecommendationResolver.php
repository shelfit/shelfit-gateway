<?php

namespace App\GraphQL\Resolver;

use App\Security\LoggedInUserAwareTrait;
use App\Service\RecommendationService;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

readonly class RecommendationResolver implements QueryInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private RecommendationService $recommendationService,
        private LoggerInterface $logger,
        private Security $security,
    ) {
    }

    public function recommendBooks(Argument $args): array
    {
        try {
            self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        try {
            $query = $args->offsetGet("query");
            return $this->recommendationService->getRecommendations($query);
        } catch (Throwable $t) {
            $this->logger->error("Can't get recommendation: {$t->getMessage()}", ['exception' => $t]);
            throw new UserError("recommendation.error");
        }
    }
}