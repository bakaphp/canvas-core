<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Baka\Contracts\Auth\UserInterface;
use function Baka\isJson;
use Canvas\Enums\NotificationChannels as NotificationChannelsEnum;
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
     * Get current channels.
     *
     * @return array
     */
    public function getChannels() : array
    {
        return isJson($this->channels) ? json_decode($this->channels, true) : [];
    }

    /**
     * Check if the given channel is enabled.
     *
     * @param string $channel
     *
     * @return bool
     */
    public function isChannelEnabled(string $channel) : bool
    {
        $channels = $this->getChannels();

        return isset($channels[$channel]);
    }

    /**
     * Remove a channel.
     *
     * @param string $channel
     *
     * @return void
     */
    public function removeChannel(string $channel) : void
    {
        $channels = $this->getChannels();

        if ($this->isChannelEnabled($channel)) {
            unset($channels[$channel]);
        }

        $this->channels = json_encode($channels);
    }

    /**
     * Set a channel.
     *
     * @param string $channel
     *
     * @return void
     */
    public function addChannel(string $channel) : void
    {
        $channels = $this->getChannels();
        $channels[$channel] = $channel;

        $this->channels = json_encode($channels);
    }

    /**
     * Set enable status.
     *
     * @param bool $status
     * @param string|null $channel
     *
     * @return void
     */
    public function setEnabledStatus(bool $status, ?string $channel = null) : void
    {
        if ($channel && !$this->isChannelEnabled($channel)) {
            $this->addChannel($channel);
            $this->is_enabled = (int) true;
        } elseif ($channel && $this->isChannelEnabled($channel)) {
            $this->removeChannel($channel);
        } else {
            $this->channels = '';
        }

        if ($channel === null || count($this->getChannels()) === 0) {
            $this->is_enabled = (int) $status;
        }
    }

    /**
     * Is the current notification type enabled for the current user.
     *
     * @param Apps $app
     * @param UserInterface $user
     * @param NotificationType $notificationType
     * @param string|null $channel
     *
     * @return bool
     */
    public static function isEnabled(
        Apps $app,
        UserInterface $user,
        NotificationType $notificationType,
        ?string $channel = null
    ) : bool {
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
            if ($channel === null || empty($setting->getChannels())) {
                return (bool) $setting->is_enabled;
            } else {
                return (bool) $setting->isChannelEnabled($channel);
            }
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
    public function muteAll(Apps $app, UserInterface $user, ?string $channelSlug = null) : bool
    {
        $params = [
            'conditions' => 'is_published = :is_published: AND apps_id = :apps_id:',
            'bind' => [
                'is_published' => 1,
                'apps_id' => $app->getId()
            ]
        ];

        if ($channelSlug) {
            $notificationChannelId = NotificationChannelsEnum::getValueBySlug($channelSlug);
            $params['conditions'] .= ' AND \\notification_channel_id = :notification_channel_id:';
            $params['bind']['notification_channel_id'] = $notificationChannelId;
        }

        $notificationTypes = NotificationType::find($params);

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
                'channels' => $channelSlug ? $this->removeChannel($channelSlug) : null,
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
    public static function listOfNotifications(Apps $app, UserInterface $user, int $parent = 0, ?string $channelSlug = null) : array
    {
        $params = [
            'conditions' => 'is_published = :is_published:
                AND parent_id = :parent_id:
                AND apps_id = :apps_id:',
            'bind' => [
                'is_published' => 1,
                'parent_id' => $parent,
                'apps_id' => $app->getId(),
            ],
            'order' => 'weight ASC'
        ];

        if ($channelSlug && $parent !== 0) {
            $notificationChannelId = NotificationChannelsEnum::getValueBySlug($channelSlug);
            $params['conditions'] .= ' AND \\notification_channel_id IN (:notification_channel_id: , 0)';
            $params['bind']['notification_channel_id'] = $notificationChannelId;
        }

        $notificationType = NotificationType::find($params);
        $userNotificationList = [];
        $i = 0;

        foreach ($notificationType as $notification) {
            $userNotificationList[$i] = [
                'name' => $notification->name,
                'description' => $notification->description,
                'notifications_types_id' => $notification->getId(),
                'is_enabled' => (int) self::isEnabled(
                    $app,
                    $user,
                    $notification,
                    $channelSlug
                ),
                'channel' => $notification->channel
            ];

            $userNotificationList[$i]['children'] = self::listOfNotifications(
                $app,
                $user,
                $notification->getId(),
                $channelSlug
            );
            $i++;
        }

        return $userNotificationList;
    }
}
