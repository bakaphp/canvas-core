<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Social\Apple\ASDecoder;

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

        $this->hasMany(
            'id',
            UserLinkedSources::class,
            'source_id',
            ['alias' => 'linkedSource']
        );
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
     * Get source by title.
     *
     * @param string $title
     *
     * @return Sources
     */
    public static function getByTitle(string $title) : Sources
    {
        return self::findFirstOrFail([
            'conditions' => 'title = :title: AND is_deleted = 0',
            'bind' => [
                'title' => $title
            ]
        ]);
    }
}
