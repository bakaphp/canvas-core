<?php
declare(strict_types=1);

namespace Canvas\Auth\Social\Providers;

use Canvas\Contracts\Auth\SocialProviderInterface;
use Phalcon\Di;

class Google implements SocialProviderInterface
{
    /**
     * getInfo.
     *
     * @param  string $token
     *
     * @return array
     */
    public function getInfo(string $token) : array
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
