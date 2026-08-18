<?php

namespace App\Service;

use Redis;

readonly class FeedCacheService
{
    private const BATCH_SIZE = 1000;
    private const FEED_SIZE_LIMIT = 500;

    public function __construct(
        private Redis $redis,
    ) {
    }

    /**
     * @param int[] $followerIds
     */
    public function cacheFeedPosts(array $followerIds, int $postId, int $timestamp): void
    {
        foreach (array_chunk($followerIds, self::BATCH_SIZE) as $followerIdBatch) {
            $pipe = $this->redis->multi(Redis::PIPELINE);
            foreach ($followerIdBatch as $followerId) {
                $key = self::cacheKey($followerId);
                $pipe->zAdd($key, ['NX'], $timestamp, $postId)
                    ->zRemRangeByRank($key, 0, -self::FEED_SIZE_LIMIT);
            }
            $pipe->exec();
        }
    }

    private static function cacheKey(int $userId): string
    {
        return "feed:$userId";
    }
}