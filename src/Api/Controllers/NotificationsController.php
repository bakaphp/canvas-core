<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Notifications;
use Canvas\Dto\Notification as NotificationDto;
use Canvas\Mapper\NotificationMapper;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class NotificationsController extends BaseController
{
    use ProcessOutputMapperTrait;
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
            ['users_id', ':', $this->userData->getId()],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }
}
