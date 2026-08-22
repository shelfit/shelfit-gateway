<?php

namespace App\Service;

use Redis;

readonly class FeedCacheService
{
    private const BATCH_SIZE = 1000;
    private const FEED_TTL = 7 * 86400; // 7 days
    public const FEED_SIZE_LIMIT = 500;

    private const FANOUT_WRITE_SCRIPT = <<<'LUA'
        if redis.call('EXISTS', KEYS[1]) == 0 then return 0 end
        redis.call('ZADD', KEYS[1], 'NX', ARGV[1], ARGV[2])
        redis.call('ZREMRANGEBYRANK', KEYS[1], 0, ARGV[3])
        return 1
    LUA;


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
                $pipe->eval(
                    self::FANOUT_WRITE_SCRIPT,
                    [self::cacheKey($followerId), $timestamp, $postId, -(self::FEED_SIZE_LIMIT+1)],
                    1
                );
            }
            $pipe->exec();
        }
    }

    /**
     * @return int[]
     */
    public function getFeedForUser(int $userId, int $limit, int $offset): array
    {
        $key = self::cacheKey($userId);
        $this->redis->expire($key, self::ttl());

        return $this->redis->zRevRange($key, $offset, $offset + $limit - 1);
    }

    /**
     * @param array{int: int} $posts
     */
    public function buildUserFeed(int $userid, array $posts): void
    {
        $key = self::cacheKey($userid);

        $tx = $this->redis->multi();
        $tx->del($key);

        foreach ($posts as $postId => $timestamp) {
            $tx->zAdd($key, ['NX'], $timestamp, $postId);
        }

        $tx->expire($key, self::ttl());
        $tx->exec();
    }

    /**
     * @param int[] $followerIds
     */
    public function removePostFromCache(array $followerIds, int $postId): void
    {
        foreach (array_chunk($followerIds, self::BATCH_SIZE) as $followerIdBatch) {
            $pipe = $this->redis->multi(Redis::PIPELINE);
            foreach ($followerIdBatch as $followerId) {
                $pipe->zRem(self::cacheKey($followerId), $postId);
            }
            $pipe->exec();
        }
    }

    private static function cacheKey(int $userId): string
    {
        return "feed:$userId";
    }

    private static function ttl(): int
    {
        return self::FEED_TTL + rand(0, (self::FEED_TTL / 10)); // add a 10% jitter to the ttl
    }
}