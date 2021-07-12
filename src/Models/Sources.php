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
}
