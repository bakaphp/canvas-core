<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Social\Apple\ASDecoder;
use Baka\Database\Model;
use Baka\Http\Exception\NotFoundException;

class Sources extends Model
{
    public string $title;
    public string $url;
    public ?int $language_id = null;
    public int $pv_order;
    public int $ep_order;

    const APPLE = 'apple';
    const FACEBOOK = 'facebook';
    const GOOGLE = 'google';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('sources');

        $this->hasMany('id', 'Canvas\Models\UserLinkedSources', 'source_id', ['alias' => 'linkedSource']);
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
     * Get a source by its title.
     */
    public static function getByTitle(string $title) : Sources
    {
        $sourceData = self::findFirstByTitle($title);

        if (!$sourceData) {
            throw new NotFoundException(_('Importing site is not currently supported.'));
        }

        return $sourceData;
    }
}
