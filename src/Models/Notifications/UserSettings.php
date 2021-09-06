<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Baka\Contracts\Auth\UserInterface;
use Canvas\Models\AbstractModel;
use Canvas\Models\Apps;
use Canvas\Models\NotificationType;

class UserSettings extends AbstractModel
{
    public int $users_id;
    public int $apps_id;
    public int $notifications_types_id;
    public int $is_enabled = 1;
    public ?string $channels = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_notification_settings');

        $this->belongsTo(
            'notifications_types_id',
            NotificationType::class,
            'id',
            [
                'alias' => 'notification',
            ]
        );
    }

    /**
     * Is the current notification type enabled for the current user.
     *
     * @param Apps $app
     * @param UserInterface $user
     * @param NotificationType $notificationType
     *
     * @return bool
     */
    public static function isEnabled(Apps $app, UserInterface $user, NotificationType $notificationType) : bool
    {
        $setting = self::findFirst([
            'conditions' => 'users_id = :users_id: AND apps_id = :apps_id: AND \notifications_types_id = :notifications_types_id:  AND is_deleted = 0',
            'bind' => [
                'users_id' => $user->getId(),
                'apps_id' => $app->getId(),
                'notifications_types_id' => $notificationType->getId(),
            ],
        ]);

        if (is_object($setting)) {
            return (bool) $setting->is_enabled;
        }

        return true;
    }

    /**
     * Get user notification settings by type.
     *
     * @param Apps $app
     * @param UserInterface $user
     * @param NotificationType $notificationType
     *
     * @return self|null
     */
    public static function getByUserAndNotificationType(Apps $app, UserInterface $user, NotificationType $notificationType) : ?self
    {
        return self::findFirst([
            'conditions' => 'users_id = :users_id: AND apps_id = :apps_id: AND \notifications_types_id = :notifications_types_id:  AND is_deleted = 0',
            'bind' => [
                'users_id' => $user->getId(),
                'apps_id' => $app->getId(),
                'notifications_types_id' => $notificationType->getId(),
            ],
        ]);
    }

    /**
     * Mute all setting for the current user.
     *
     * @param Apps $app
     * @param UserInterface $user
     *
     * @return bool
     */
    public function muteAll(Apps $app, UserInterface $user) : bool
    {
        return $this->di->get('db')->prepare(
            'UPDATE users_notification_settings SET is_enabled = 0 WHERE users_id = :users_id AND apps_id = :apps_id AND is_deleted = 0',
        )->execute([
            'users_id' => $user->getId(),
            'apps_id' => $app->getId()
        ]);
    }

    /**
     * Undocumented function.
     *
     * @param Apps $app
     * @param UserInterface $user
     * @param NotificationType $notificationType
     *
     * @return array
     */
    public static function listOfNotifications(Apps $app, UserInterface $user, int $parent = 0) : array
    {
        $notificationType = NotificationType::find('parent_id = ' . $parent . ' and apps_id =' . $app->getId());
        $userNotificationList = [];
        $i = 0;
        foreach ($notificationType as $notification) {
            $userNotificationList[$i] = [
                'name' => $notification->name,
                'description' => $notification->description,
                'notifications_types_id' => $notification->getId(),
                'is_enabled' => (int) self::isEnabled($app, $user, $notification),
            ];
            $userNotificationList[$i]['children'] = self::listOfNotifications($app, $user, $notification->getId());
            $i++;
        }

        return $userNotificationList;
    }
}
