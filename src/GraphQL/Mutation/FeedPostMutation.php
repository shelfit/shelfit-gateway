<?php

namespace App\GraphQL\Mutation;

use App\Entity\FeedPost;
use App\Entity\FeedPostLike;
use App\Entity\FeedPostType;
use App\Repository\FeedPostLikeRepository;
use App\Repository\FeedPostRepository;
use App\Security\LoggedInUserAwareTrait;
use App\Service\AuthorizationService;
use App\Service\FeedService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class FeedPostMutation implements MutationInterface
{
    use LoggedInUserAwareTrait;

    public function __construct(
        private Security $security,
        private FeedService $feedService,
        private FeedPostRepository $feedPostRepository,
        private FeedPostLikeRepository $feedPostLikeRepository,
        private EntityManagerInterface $entityManager,
        private AuthorizationService $authorizationService,
    ) {
    }

    public function createTextPost(Argument $args): FeedPost
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        return $this->feedService->createTextPost($args->offsetGet('text'), $user);
    }

    public function updateTextPost(Argument $args): FeedPost
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $post = $this->feedPostRepository->findOneBy([
            'id' => $args->offsetGet('postId'),
            'deleted' => false
        ]);
        if ($post === null) {
            throw new UserError('post.not.found');
        }

        if (!$this->authorizationService->authorizeResourceOwnership($post->getUser()->getId(), $user)) {
            throw new UserError('not.authorized');
        }

        if ($post->getType() !== FeedPostType::TYPE_TEXT) {
            throw new UserError('post.type.not.text');
        }

        $post->setText($args->offsetGet('text'));
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        return $post;
    }

    public function deleteFeedPost(Argument $args): bool
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $post = $this->feedPostRepository->findOneBy([
            'id' => $args->offsetGet('postId'),
            'deleted' => false
        ]);
        if ($post === null) {
            throw new UserError('post.not.found');
        }

        if (!$this->authorizationService->authorizeResourceOwnership($post->getUser()->getId(), $user)) {
            throw new UserError('not.authorized');
        }

        return $this->feedService->deleteFeedPost($post);
    }

    public function likePost(Argument $args): ?FeedPostLike
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $feedPost = $this->feedPostRepository->findOneBy([
            'id' => $args->offsetGet('feedPostId'),
            'deleted' => false
        ]);
        if ($feedPost === null) {
            throw new UserError('feed.post.not.found');
        }

        $like = $this->feedPostLikeRepository->findOneBy(['user' => $user, 'feedPost' => $feedPost]);
        if ($like !== null) {
            return $like;
        }

        try {
            return $this->feedService->likeFeedPost($feedPost, $user);
        } catch (UniqueConstraintViolationException) {
            return null;
        }
    }

    public function unlikePost(Argument $args): bool
    {
        try {
            $user = self::getLoggedInUser($this->security);
        } catch (AuthenticationException) {
            throw new UserError(self::NOT_AUTHENTICATED);
        }

        $like = $this->feedPostLikeRepository->find((int)$args->offsetGet('feedPostLikeId'));
        if ($like === null) {
            throw new UserError('feed.post.like.not.found');
        }

        if (!$this->authorizationService->authorizeResourceOwnership($like->getUser()->getId(), $user)) {
            throw new UserError('not.authorized');
        }

        $this->entityManager->remove($like);
        $this->entityManager->flush();
        return true;
    }
}