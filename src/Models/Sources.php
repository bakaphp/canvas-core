<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Social\Apple\ASDecoder;
use Baka\Auth\Models\Sources as BakaSource;

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
}
