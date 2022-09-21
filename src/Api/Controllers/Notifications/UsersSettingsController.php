<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Notifications;

use Canvas\Api\Controllers\BaseController;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Notifications\UserSettings as NotificationsUserSettings;
use Canvas\Enums\Notification;
use Canvas\Mapper\Notifications\UserSettings as MapperNotificationsUserSettings;
use Canvas\Models\Notifications\UserSettings;
use Canvas\Models\NotificationType;
use Phalcon\Http\Response;

class UsersSettingsController extends BaseController
{
    use ProcessOutputMapperTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'notifications_types_id',
        'is_enabled',
        'channels',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'notifications_types_id',
        'is_enabled',
        'channels'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new UserSettings();
        $this->dto = NotificationsUserSettings::class;
        $this->dtoMapper = new MapperNotificationsUserSettings();
        $this->model->users_id = $this->userData->getId();
        $this->model->apps_id = $this->app->getId();

        $this->additionalSearchFields = [
            ['users_id', ':', $this->userData->getId()],
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Given the model list the records based on the  filter.
     *
     * @return Response
     */
    public function listAll(int $userId) : Response
    {
        $channelName = $this->request->hasQuery('channel') ? $this->request->getQuery('channel', 'string') : null;
        return $this->response(UserSettings::listOfNotifications($this->app, $this->userData, 0, $channelName));
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
     * Given a notification type id , set or create the notification settings.
     *
     * @param int $userId
     * @param int $notificationId
     *
     * @return Response
     */
    public function setNotificationSettings(int $userId, int $notificationTypeId) : Response
    {
        $notificationType = NotificationType::findFirstOrFail($notificationTypeId);

        if (!$notificationSettings = UserSettings::getByUserAndNotificationType($this->app, $this->userData, $notificationType)) {
            $notificationSettings = new UserSettings();
            $notificationSettings->users_id = $this->userData->getId();
            $notificationSettings->apps_id = $this->app->getId();
            $notificationSettings->notifications_types_id = $notificationTypeId;
            $notificationSettings->is_enabled = (int) false; //if its the first time its to turn it off
            $notificationSettings->channels = json_encode([]);
        } else {
            $notificationSettings->is_enabled = (int) !$notificationSettings->is_enabled;
        }
        $notificationSettings->saveOrFail();
        $this->userData->set(Notification::getValueBySlug($notificationType->channel->slug), 0);

        return $this->response($this->processOutput($notificationSettings));
    }

    /**
     * Clear all settings.
     *
     * @param int $userId
     *
     * @return Response
     */
    public function muteAll(int $userId) : Response
    {
        $channelName = $this->request->hasQuery('channel') ? $this->request->getQuery('channel', 'string') : null;
        $this->model->muteAll($this->app, $this->userData, $channelName);
        $this->userData->set(Notification::getValueBySlug($channelName), 1);

        return $this->response('All Notifications are muted');
    }
}
