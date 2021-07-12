<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Notification as NotificationDto;
use Canvas\Mapper\NotificationMapper;
use Canvas\Models\Notifications;
use Phalcon\Http\Response;

class NotificationsController extends BaseController
{
    use ProcessOutputMapperTrait{
        processOutput as protected parentProcessOutput;
    }
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['read'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['read'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Notifications();
        $this->dto = NotificationDto::class;
        $this->dtoMapper = new NotificationMapper();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
            ['users_id', ':', $this->userData->getId()],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }

    /**
     * Clean all the notifications of a user.
     *
     * @return Response
     */
    public function cleanAll() : Response
    {
        Notifications::markAsRead($this->userData);

        return $this->response(['Cleaned All Notifications']);
    }

    /**
     * Overwrite processOutput.
     *
     * @param mixed $results
     *
     * @return mixed
     */
    protected function processOutput($results)
    {
        $results = $this->parentProcessOutput($results);

        //if we are using format for listing we send the the total notifications
        if (is_array($results) && isset($results['data'])) {
            $results['total_notifications'] = Notifications::totalUnRead($this->userData);
        }

        return $results;
    }
}
