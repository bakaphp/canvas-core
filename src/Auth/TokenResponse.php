<?php

declare(strict_types=1);

namespace Canvas\Auth;

use Baka\Contracts\Auth\UserInterface;

class TokenResponse
{
    /**
     * Get the Token  Auth Response for JWT auth.
     *
     * @return array
     */
    public static function create(UserInterface $user) : array
    {
        $token = $user->getToken();

        return self::format($user, $token);
    }

    /**
     * Given a token format it to the standard response.
     *
     * @param UserInterface $user
     * @param array $token
     *
     * @return array
     */
    public static function format(UserInterface $user, array $token) : array
    {
        return [
            'sessionId' => $token['sessionId'],
            'token' => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'time' => date('Y-m-d H:i:s'),
            'expires' => $token['token_expiration'],
            'refresh_token_expires' => $token['refresh_token_expiration'],
            'id' => $user->getId(),
            'timezone' => $user->timezone
        ];
    }
}
