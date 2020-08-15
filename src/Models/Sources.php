<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\ASDecoder;
use Canvas\Http\Exception\InternalServerErrorException;
use Facebook\Facebook;
use Google_Client;
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
        switch ($this->title) {
            case 'google':
                try {
                    $client = new Google_Client([
                        'client_id' => getenv('GOOGLE_CLIENT_ID')
                    ]);
                    $payload = $client->verifyIdToken($token);
                    if ($payload) {
                        $userid = $payload['sub'];
                        return $payload['email'] === $email;
                    } else {
                        return false;
                    }
                } catch (Throwable $e) {
                    return false;
                }
                break;
            case 'facebook':
                try {
                    $fb = new Facebook([
                        'app_id' => getenv('FACEBOOK_APP_ID'),
                        'app_secret' => getenv('FACEBOOK_APP_SECRET'),
                        'default_graph_version' => 'v8.0',
                        // . . .
                    ]);
                    $response = $fb->get('/me', $token);
                    $user = $response->getGraphUser();
                    if ($user) {
                        return true;
                    }
                    return false;
                } catch (Exception $e) {
                    return false;
                }
            break;
        }
    }
}
