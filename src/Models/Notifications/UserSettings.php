<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Baka\Contracts\Auth\UserInterface;
use Canvas\Models\AbstractModel;
use Canvas\Models\Apps;
use Canvas\Models\NotificationType;
use Canvas\Models\NotificationChannels;
use Canvas\Enums\NotificationChannels as NotificationChannelsEnum;

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
            'conditions' => 'users_id = :users_id: 
                            AND apps_id = :apps_id: 
                            AND \notifications_types_id = :notifications_types_id: 
                            AND is_deleted = 0',
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
            'conditions' => 'users_id = :users_id: 
                            AND apps_id = :apps_id: 
                            AND \notifications_types_id = :notifications_types_id: 
                            AND is_deleted = 0',
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
        $notificationTypes = NotificationType::find('is_published = 1 AND apps_id =' . $app->getId());

        foreach ($notificationTypes as $notificationType) {
            self::updateOrCreate([
                'conditions' => 'users_id = :users_id: 
                                AND apps_id = :apps_id: 
                                AND \notifications_types_id = :notifications_types_id:',
                'bind' => [
                    'users_id' => $user->getId(),
                    'apps_id' => $notificationType->apps_id,
                    'notifications_types_id' => $notificationType->getId(),
                ],
            ], [
                'is_enabled' => 0,
                'users_id' => $user->getId(),
                'apps_id' => $notificationType->apps_id,
                'notifications_types_id' => $notificationType->getId()
            ]);
        }

        return true;
    }

    /**
     * List of the notifications that are published function.
     *
     * @param Apps $app
     * @param UserInterface $user
     * @param NotificationType $notificationType
     * @param string $channelSlug
     *
     * @return array
     */
    public static function listOfNotifications(Apps $app, UserInterface $user, int $parent = 0, string $channelSlug = null) : array
    {
        $params = [
            "conditions" => 
                "is_published = :is_published:
                AND parent_id = :parent_id:
                AND apps_id = :apps_id:",
            "bind" => [
                "is_published" => 1,
                "parent_id" => $parent,
                "apps_id" => $app->getId(),
                ]
        ];

        if ($channelSlug) {
            $notificationChannel = NotificationChannelsEnum::getValueBySlug($channelSlug);
            $params['conditions'] .= " AND notification_channel_id = :notification_channel_id:";
            $params['bind']['notification_channel_id'] = $notificationChannel->id;
        }
        
        $notificationType = NotificationType::find($params);
        $userNotificationList = [];
        $i = 0;

        foreach ($notificationType as $notification) {
            $userNotificationList[$i] = [
                'name' => $notification->name,
                'description' => $notification->description,
                'notifications_types_id' => $notification->getId(),
                'is_enabled' => (int) self::isEnabled($app, $user, $notification),
                'channel' => $notificationType->channel
            ];
            $userNotificationList[$i]['children'] = self::listOfNotifications($app, $user, $notification->getId());
            $i++;
        }

        return $userNotificationList;
    }
}
