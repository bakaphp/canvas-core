<?php

namespace Canvas\Auth\Social\Providers;

use Canvas\Contracts\Auth\SocialProviderInterface;
use Phalcon\Di;
use Exception;

class Google implements SocialProviderInterface
{
    /**
     * getInfo
     *
     * @param  string $token
     * @return array
     */
    public function getInfo(string $token): array
    {
        $client = DI::getDefault()->get('google');
        $payload = $client->verifyIdToken($token);
        if (!$payload) {
            throw new Exception('Error getting info from access token');
        }
        return  [
            'email' => $payload['email'],
            'name' => $payload['name'],
            'social_id' => $payload['sub'],
            'identifier' => $payload['sub'],
        ];
    }
}
