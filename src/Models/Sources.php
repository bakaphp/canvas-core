<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\ASDecoder;
use Canvas\Http\Exception\InternalServerErrorException;
use Exception;
use Throwable;

/**
 * Class Resources.
 *
 * @package Canvas\Models
 *
 * @property \Phalcon\Di $di
 */
class Sources extends AbstractModel
{
    /**
     *
     * @var int
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
     * @var int
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
     * @var int
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
    public function getSource() : string
    {
        return 'sources';
    }

    /**
     * Verify if source is Apple.
     */
    public function isApple() : bool
    {
        return $this->title == 'apple';
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
        try {
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
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
