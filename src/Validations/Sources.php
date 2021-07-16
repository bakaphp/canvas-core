<?php
declare(strict_types=1);

namespace Canvas\Validations;

use Phalcon\Di;

class Sources
{
    /**
     * validation.
     *
     * @param  string $email
     * @param  string $token
     *
     * @return bool
     */
    public static function validation(string $title, string $email, string $token) : bool
    {
        $di = DI::getDefault();
        switch ($title) {
                case 'google':
                        $client = $di->get('google');
                        $payload = $client->verifyIdToken($token);
                        if ($payload) {
                            $userid = $payload['sub'];
                            return $payload['email'] === $email;
                        } else {
                            throw new Exception('Invalid user on google validation, payload or email incorrect');
                        }
                    break;
                case 'facebook':
                        $fb = $di->get('facebook');
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
