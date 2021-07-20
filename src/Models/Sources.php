<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\NotFoundException;
use Baka\Social\Apple\ASDecoder;
use Phalcon\Di;

class Sources extends Model
{
    public string $title;
    public string $url;
    public ?int $language_id = null;
    public int $pv_order;
    public int $ep_order;
    public int $use_validation;
    public ?string $validation_class = null;

    const APPLE = 'apple';
    const FACEBOOK = 'facebook';
    const GOOGLE = 'google';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('sources');

        $this->hasMany('id', UserLinkedSources::class, 'source_id', ['alias' => 'linkedSource']);
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


    /*
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
    
    /**
    * getUseValidation
    *
    * @return bool
    */
    public function getUseValidation(): int
    {
        return $this->use_validation;
    }

    /**
     * getValidationClass
     *
     * @return string
     */
    public function getValidationClass(): ?string
    {
        return $this->validation_class;
    }
}
