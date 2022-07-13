<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Users;

use Canvas\Api\Controllers\BaseController;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\User as UserDto;
use Canvas\Mapper\UserMapper;
use Canvas\Models\Users;
use Canvas\Models\Users\UsersDeletionRequest;
use Phalcon\Http\Response;

class DeletionRequestController extends BaseController
{
    use ProcessOutputMapperTrait;

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Users();
        $this->dto = UserDto::class;
        $this->dtoMapper = new UserMapper();
    }

    /**
     * Request account deletion.
     *
     * @param string $id
     *
     * @return Response
     */
    public function requestDeletion(string $id) : Response
    {
        $request = $this->request->getPostData();

        UsersDeletionRequest::updateOrCreate(
            [
                'conditions' => 'users_id = :users_id: AND apps_id = :apps_id: AND is_deleted = 0',
                'bind' => [
                    'users_id' => $this->userData->getId(),
                    'apps_id' => $this->app->getId(),
                ]
            ],
            [
                'users_id' => $this->userData->getid(),
                'apps_id' => $this->app->getId(),
                'email' => $this->userData->email,
                'data' => $request['data'] ?? '',
                'request_date' => date('Y-m-d H:i:s'),
            ]
        );

        $this->userData->set('delete_requested', 1);

        return $this->response(
            $this->processOutput($this->userData)
        );
    }

    /**
     * Reactivate Account.
     *
     * @param string $id
     *
     * @return Response
     */
    public function requestActivate(string $id) : Response
    {
        if ((int) $this->userData->get('delete_requested') === 1) {
            $this->userData->set('delete_requested', 0);

            $requests = UsersDeletionRequest::find([
                'conditions' => 'users_id = :users_id: AND apps_id = :apps_id: AND is_deleted = 0',
                'bind' => [
                    'users_id' => $this->userData->getId(),
                    'apps_id' => $this->app->getId(),
                ]
            ]);

            if ($requests->count()) {
                foreach ($requests as $request) {
                    $request->softDelete();
                }
            }
        }

        return $this->response(
            $this->processOutput($this->userData)
        );
    }
}
