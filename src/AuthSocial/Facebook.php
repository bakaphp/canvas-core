<?php

namespace Canvas\AuthSocial;

use Canvas\Contracts\Auth\SocialInterface;
use Phalcon\Di;
use Exception;

class Google implements SocialInterface
{
    /**
     * getInfo
     *
     * @param  string $token
     * @return array
     */
    public function getInfo(string $token): array
    {
        $client = DI::getDefault()->get('facebook');
        $response = $client->get('/me?fields=name,email,cover', $token);
        $payload = $response->getGraphUser();
        return [
            'email' => $payload->getField('email'),
            'name' => $payload->getField('name'),
            'social_id' => $payload->getField('id'),
            'identifier' => $payload->getField('id'),
        ];
    }
}
