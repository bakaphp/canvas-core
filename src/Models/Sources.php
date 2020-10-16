<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Auth\Models\Sources as BakaSource;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Social\Apple\ASDecoder;
use Phalcon\Di;

class Sources extends BakaSource
{
    public string $title;
    public string $url;
    public ?int $language_id = null;

    const APPLE = 'apple';
    const FACEBOOK = 'facebook';
    const GOOGLE = 'google';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('sources');
    }

    /**
     * Verify if source is Apple.
     */
    public function isApple() : bool
    {
        return $this->title == self::APPLE;
    }

    /**
     * Validate Apple User.
     *
     * @param string $identityToken
     *
     * @return object
     */
    public function validateAppleUser(string $identityToken) : object
    {
        $appleUserInfo = ASDecoder::getAppleSignInPayload($identityToken);

        if (!is_object($appleUserInfo)) {
            throw new InternalServerErrorException('Apple user not valid');
        }

        return $appleUserInfo;
    }

    /**
     * validation.
     *
     * @param  string $email
     * @param  string $token
     *
     * @return bool
     */
    public function validation(string $email, string $token) : bool
    {
        $di = DI::getDefault();
        switch ($this->title) {
                case 'google':
                        $client = $di->getGoogle();
                        $payload = $client->verifyIdToken($token);
                        if ($payload) {
                            $userid = $payload['sub'];
                            return $payload['email'] === $email;
                        } else {
                            throw new Exception('Invalid user on google validation, payload or email incorrect');
                        }
                    break;
                case 'facebook':
                        $fb = $di->getFacebook();
                        $response = $fb->get('/me', $token);
                        $user = $response->getGraphUser();
                        if ($user) {
                            return true;
                        }
                        throw new Exception('Invalid user on facebook validation');
                break;
        }
    }
}
