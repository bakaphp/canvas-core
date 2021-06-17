<?php

namespace Canvas\Tests\integration\Contracts\Models;

use Canvas\Contracts\Models\CacheKeys;
use IntegrationTester;
use Phalcon\Di;

class CacheKeysCest
{
    use CacheKeys{
        generateCacheKey as protected;
        clearCacheByKeyPattern as protected;
        clearAllCacheByPattern as protected;
    }

    public function validateCacheKeyGenerator(IntegrationTester $I)
    {
        $params = [
            'key' => 'value',
            'user_id' => 1
        ];

        $key = self::generateCacheKey($params);

        $I->assertStringContainsString('key:value_user_id:1', $key);
    }

    public function validateClearCacheByKey(IntegrationTester $I)
    {
        $redis = Di::getDefault()->get('redis');
        $key = 'mc_test_something_';
        $redis->set($key . time(), 'test');

        $I->assertTrue(1 >= self::clearCacheByKeyPattern($key));
    }
}
