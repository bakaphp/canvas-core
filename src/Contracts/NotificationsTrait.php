<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use Canvas\Models\Notifications;
use Canvas\Models\SystemModules;
use Phalcon\Di;

trait NotificationsTrait
{
    /**
     * Create a new notification.
     *
     * @param array $user
     * @param string $content
     * @param int $notificationTypeId
     * @param string $systemModule
     *
     * @return void
     */
    public static function create(array $entity, array $user, string $content, int $notificationTypeId, string $systemModule) : void
    {
        $notification = new Notifications();
        $notification->users_id = $user['id'];
        $notification->companies_id = $user['default_company'];
        $notification->apps_id = Di::getDefault()->getApp()->getId();
        $notification->notification_type_id = $notificationTypeId;
        $notification->system_module_id = SystemModules::getByModelName($systemModule)->id;
        $notification->entity_id = $entity['id'];
        $notification->content = $content;
        $notification->created_at = date('Y-m-d H:i:s');

        if (!$notification->save()) {
            Di::getDefault()->getLog()->error((string) current($notification->getMessages()));
        }
    }
}
