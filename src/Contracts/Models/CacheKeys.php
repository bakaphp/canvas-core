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
     * Given a key pattern remove clear all redis data.
     *
     * @param string $key
     *
     * @return int
     */
    public static function clearCacheByKeyPattern(string $key) : int
    {
        $redis = Di::getDefault()->get('modelsCache');

        $redisKeysList = $redis->getKeys($key . '*');
        $total = 0;

        foreach ($redisKeysList as $redisKey) {
            $redisKey = str_replace(Di::getDefault()->get('app')->key, '', $redisKey);
            $total += $redis->delete($redisKey);
        }

        return $total;
    }
}
