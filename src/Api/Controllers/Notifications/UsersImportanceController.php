<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Notifications;

use Canvas\Api\Controllers\BaseController;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Notifications\UserImportance;
use Canvas\Mapper\Notifications\UserImportance as NotificationsUserImportance;
use Canvas\Models\Notifications\Importance;
use Canvas\Models\Notifications\UserEntityImportance;
use Canvas\Models\SystemModules;
use Phalcon\Http\Response;

class UsersImportanceController extends BaseController
{
    use ProcessOutputMapperTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'system_modules_id',
        'importance_id',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'notifications_types_id',
        'importance_id',
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UserEntityImportance();
        $this->dto = UserImportance::class;
        $this->dtoMapper = new NotificationsUserImportance();
        $this->model->users_id = $this->userData->getId();
        $this->model->apps_id = $this->app->getId();

        $this->additionalSearchFields = [
            ['users_id', ':', $this->userData->getId()],
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Get a notification by it type.
     *
     * @param int $id
     *
     * @return Response
     */
    public function getByNotificationId(int $userId, int $notificationTypeId) : Response
    {
        $this->additionalSearchFields[] = [
            ['notifications_types_id', ':', $notificationTypeId]
        ];

        $results = $this->processIndex();

        //return the response + transform it if needed
        return $this->response(!empty($results) ? $results[0] : []);
    }

    /**
     * Create or update user entity importance.
     *
     * @param int $userId
     *
     * @return Response
     */
    public function setImportanceSettings(int $userId) : Response
    {
        $this->request->validate([
            'system_modules_id' => 'int|required',
            'importance_id' => 'int|required',
            'entity_id' => 'required',
        ]);

        $request = $this->request->getPostData();

        $systemModule = SystemModules::findFirstOrFail([
            'conditions' => 'apps_id = :apps_id: AND id = :id:',
            'bind' => [
                'apps_id' => $this->app->getId(),
                'id' => $request['system_modules_id'],
            ]
        ]);

        $notificationImportance = Importance::findFirstOrFail([
            'conditions' => 'apps_id = :apps_id: and id = :importance_id:',
            'bind' => [
                'apps_id' => $this->app->getId(),
                'importance_id' => $request['importance_id'],
            ]
        ]);

        $userEntityImportance = UserEntityImportance::updateOrCreate([
            'conditions' => '
                            apps_id = :apps_id: 
                            AND users_id = :users_id: 
                            AND system_modules_id = :system_modules_id:
                            AND entity_id = :entity_id: 
                            AND is_deleted = 0',
            'bind' => [
                'apps_id' => $this->app->getId(),
                'users_id' => $this->userData->getId(),
                'system_modules_id' => $systemModule->getId(),
                'entity_id' => $request['$entity_id']
            ]
        ], [
            'users_id' => $this->userData->getId(),
            'apps_id' => $this->app->getId(),
            'system_modules_id' => $systemModule->getId(),
            'importance_id' => $notificationImportance->getId(),
            'entity_id' => (string) $request['entity_id'],
        ]);

        return $this->response($this->processOutput($userEntityImportance));
    }
}
