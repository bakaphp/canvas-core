<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Notifications;
use Canvas\Dto\Notification;
use Canvas\Mapper\NotificationMapper;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class NotificationsController extends BaseController
{
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
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['users_id', ':', $this->userData->getId()],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }

    /**
     * Pass the resultset to a DTO Mapper.
     *
     * @param mixed $results
     * @return void
     */
    protected function processOutput($results)
    {
        $this->dtoConfig->registerMapping(Notifications::class, Notification::class)
            ->useCustomMapper(new NotificationMapper());

        return is_iterable($results) ?
        $this->mapper->mapMultiple($results, Notification::class)
        : $this->mapper->map($results, Notification::class);
    }
}
