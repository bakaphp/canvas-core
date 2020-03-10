<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UserLinkedSources;
use Baka\Auth\Models\Sources;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Validation as CanvasValidation;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use GuzzleHttp\Client;
use \AppleSignIn\ASDecoder;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 * @property UserData $userData
 *
 */
class UserLinkedSourcesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'users_id',
        'source_id',
        'source_users_id',
        'source_users_id_text',
        'source_username'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'users_id',
        'source_id',
        'source_users_id',
        'source_users_id_text',
        'source_username'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UserLinkedSources();
        $this->softDelete = 1;
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['users_id', ':', $this->userData->getId()],
        ];
    }

    /**
     * Associate a Device with the corrent loggedin user.
     *
     * @url /users/{id}/device
     * @method POST
     * @return Response
     */
    public function devices() : Response
    {
        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('app', new PresenceOf(['message' => _('App name is required.')]));
        $validation->add('deviceId', new PresenceOf(['message' => _('device ID is required.')]));
        $msg = null;

        //validate this form for password
        $validation->validate($this->request->getPost());

        $app = $this->request->getPost('app', 'string');
        $deviceId = $this->request->getPost('deviceId', 'string');

        //get the app source
        if ($source = Sources::getByTitle($app)) {
            $userSource = UserLinkedSources::findFirst([
                'conditions' => 'users_id = ?0 AND source_users_id_text = ?1 AND source_id = ?2 AND is_deleted = 0',
                'bind' => [
                    $this->userData->getId(),
                    $deviceId,
                    $source->getId()
                ]
            ]);

            if (!is_object($userSource)) {
                $userSource = new UserLinkedSources();
                $userSource->users_id = $this->userData->getId();
                $userSource->source_id = $source->getId();
                $userSource->source_users_id = $this->userData->getId();
                $userSource->source_users_id_text = $deviceId;
                $userSource->source_username = $this->userData->displayname . ' ' . $app;
                $userSource->is_deleted = 0;

                $userSource->saveOrFail();

                $msg = 'User Device Associated';
            } else {
                $msg = 'User Device Already Associated';
            }
        }

        //clean password @todo move this to a better place
        $this->userData->password = null;

        return $this->response([
            'msg' => $msg,
            'user' => $this->userData
        ]);
    }

    /**
     * Detach user's devices.
     * @param integer $id User's id
     * @param string $deviceId User's devices id
     * @return Response
     */
    public function detachDevice(int $id, string $deviceId): Response
    {
        //$sourceId = $this->request->getPost('source_id', 'int');
        $userSource = UserLinkedSources::findFirstOrFail([
            'conditions' => 'users_id = ?0  and source_users_id_text = ?1 and is_deleted = 0',
            'bind' => [$this->userData->getId(), $deviceId]
        ]);

        $userSource->softDelete();

        return $this->response([
            'msg' => 'User Device detached',
            'user' => $this->userData
        ]);
    }

    /**
     * Test Get Apple Access Tokens
     */
    public function validateAppleToken(): Response
    {

        $clientUser = getenv('APPLE_ISS');
        $identityToken = "eyJraWQiOiI4NkQ4OEtmIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLm1lbW9kYXBwLmFwcC5kZXZlbG9wbWVudC5pb3MiLCJleHAiOjE1ODM4Njk0MzksImlhdCI6MTU4Mzg2ODgzOSwic3ViIjoiMDAwNDkxLjllMzUwYzExMzg4YTQxOTc5MzU2MjYyNTJhYmNiZDg5LjE5MTciLCJub25jZSI6ImQzY2UzNWRjMzE2NzUyYjgxZDlhMjIwMzVjZWI2NWMyMzlhZTQ1NjYxOTA1YzMxMTRhZmVjYTJhZDE2Njg5NTkiLCJjX2hhc2giOiJIeC1SVGZkS0VmczFQMmRqV29hX2pnIiwiZW1haWwiOiJhbGV4dXBAbWN0ZWtrLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSIsImF1dGhfdGltZSI6MTU4Mzg2ODgzOSwibm9uY2Vfc3VwcG9ydGVkIjp0cnVlfQ.d4Tl0aGakJVsPhxqnUnQFRF1_hra9LETJDSPWXfyn-sRgJ8Tm-EnBzGU-v-weDSnJcUQktrskmIEyfe3zcMBDnlB2ao0lf4BA5Yo_9JRsnIaOk89VyFBuf52VXgWjWNYcJ-KN8G8eOcLd9cALInbgF8FBTDL0PeGzXW_1oc6944YJZVg6yui9TarAqvZxwLVRRMmzBXarvgkGNL3CctfrFisVv1nvfti0I4HMQpIlt8zcbpNXWsrx9vs3SflX5G9IwKtjTzP4wH_bfuUTTDroEu7aKM3ToZh5bsQnfMKNoCLAw5X34zKBHZ8o4lZmApSNXHudk84Uz7LNIBfoGphHg";

        $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);

        /**
         * Obtain the Sign In with Apple email and user creds.
         */
        $email = $appleSignInPayload->getEmail();
        $user = $appleSignInPayload->getUser();

        /**
         * Determine whether the client-provided user is valid.
         */
        $isValid = $appleSignInPayload->verifyUser($clientUser);

        return $this->response($isValid);

    }
}
