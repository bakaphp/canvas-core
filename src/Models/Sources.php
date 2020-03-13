<?php

declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Canvas\Exception\ModelException;
use Baka\ASDecoder;
use Canvas\Http\Exception\InternalServerErrorException;


/**
 * Class Resources
 *
 * @package Canvas\Models
 *
 * @property \Phalcon\Di $di
 */
class Sources extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var integer
     */
    public $language_id;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('sources');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'sources';
    }

    /**
     * Verify if source is Apple
     */
    public function isApple(): bool
    {
        return $this->title == 'apple';
    }

    /**
     * Validate Apple User.
     * @param string $identityToken
     * @return object
     */
    public function validateAppleUser(string $identityToken): object
    {
        $appleUserInfo = ASDecoder::getAppleSignInPayload($identityToken);

        if (!is_object($appleUserInfo)) {
            throw new InternalServerErrorException('Apple user not valid');
        }

        return $appleUserInfo;
    }
}
