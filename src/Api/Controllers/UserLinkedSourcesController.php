<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UserLinkedSources;
use Baka\Auth\Models\Sources;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Validation as CanvasValidation;
use Baka\ASDecoder;

use Canvas\Http\Exception\InternalServerErrorException;

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

            //If source is apple verify if the token is valid
            if ($source->title == 'apple') {
                $deviceId = $this->validateAppleUser($deviceId)->sub;
            }
            
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
     * Validate Apple User
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
