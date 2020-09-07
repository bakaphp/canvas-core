<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Social\Apple\ASDecoder;
use Baka\Validation as CanvasValidation;
use Canvas\Models\Sources;
use Canvas\Models\UserLinkedSources;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;

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
     *
     * @method POST
     *
     * @return Response
     */
    public function devices() : Response
    {
        //Ok let validate user password
        $validation = new CanvasValidation();
        $validation->add('app', new PresenceOf(['message' => _('App name is required.')]));
        $validation->add('deviceId', new PresenceOf(['message' => _('device ID is required.')]));
        $msg = null;

        $request = $this->request->getPostData();
        //validate this form for password
        $validation->validate($request);

        $app = $request['app'];
        $deviceId = $request['deviceId'];

        //get the app source
        if ($source = Sources::getByTitle($app)) {
            //If source is apple verify if the token is valid
            if ($source->title == Sources::APPLE) {
                $deviceId = $this->validateAppleUser($deviceId)->sub;
            }

            UserLinkedSources::updateOrCreate([
                'conditions' => 'users_id = ?0 AND source_users_id_text = ?1 AND source_id = ?2 AND is_deleted = 0',
                'bind' => [
                    $this->userData->getId(),
                    $deviceId,
                    $source->getId()
                ]
            ], [
                'users_id' => $this->userData->getId(),
                'source_id' => $source->getId(),
                'source_users_id' => $this->userData->getId(),
                'source_username' => $this->userData->displayname . ' ' . $app,
                'is_deleted' => 0,
                'source_users_id_text' => $deviceId,
            ]);

            $msg = 'User Device Associated';
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
     *
     * @param int $id User's id
     * @param string $deviceId User's devices id
     *
     * @return Response
     */
    public function detachDevice(int $id, string $deviceId) : Response
    {
        //$sourceId = $this->request->getPost('source_id', 'int');
        $userSource = UserLinkedSources::findFirstOrFail([
            'conditions' => 'users_id = ?0  and source_users_id_text = ?1 and is_deleted = 0',
            'bind' => [
                $this->userData->getId(),
                $deviceId
            ]
        ]);

        $userSource->softDelete();

        return $this->response([
            'msg' => 'User Device detached',
            'user' => $this->userData
        ]);
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
