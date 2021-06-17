<?php

declare(strict_types=1);

namespace Canvas\Contracts\Models;

use Phalcon\Di;

trait CacheKeys
{
    /**
     * Generate a cache key.
     *
     * @param array $params
     *
     * @return string
     */
    public static function generateCacheKey(array $params) : string
    {
        $uniqueKey = [];

        foreach ($params as $key => $value) {
            if (true === is_scalar($value)) {
                $uniqueKey[] = $key . ':' . $value;
            } elseif (true === is_array($value)) {
                $uniqueKey[] = sprintf(
                    '%s:[%s]',
                    $key,
                    self::generateCacheKey($value)
                );
            }
        }

        return strtolower(str_replace('\\', '_', self::class) . join('_', $uniqueKey));
    }

    /**
     * Clear all cache by a patter of app and key.
     *
     * @param string $provider
     * @param string $key
     * @param string $appKey
     *
     * @return int
     */
    public static function clearAllCacheByPattern(string $provider, string $key, string $appKey) : int
    {
        if (!Di::getDefault()->has($provider)) {
            return 0;
        }

        $redis = Di::getDefault()->get($provider);

        $redisKeysList = $provider == 'modelsCache' ? $redis->getKeys($key) : $redis->keys($key);

        $total = 0;

        foreach ($redisKeysList as $redisKey) {
            $redisKey = str_replace($appKey, '', $redisKey);
            $total += $provider == 'modelsCache' ? $redis->delete($redisKey) : $redis->del($redisKey);
        }

        return $total;
    }

    /**
     * Given a key pattern remove clear all redis data.
     *
     * @param string $key
     *
     * @return int
     */
    public static function clearCacheByKeyPattern(string $key) : int
    {
        //redis provider
        $total = self::clearAllCacheByPattern('redis', $key . '*', Di::getDefault()->get('app')->key . ':');
        //model cache cache
        $total += self::clearAllCacheByPattern('modelsCache', $key, Di::getDefault()->get('app')->key);

        return $total;
    }
}
