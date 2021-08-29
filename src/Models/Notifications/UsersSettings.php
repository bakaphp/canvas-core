<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Canvas\Models\AbstractModel;

class UsersSettings extends AbstractModel
{
    public int $users_id;
    public int $apps_id;
    public int $notifications_types_id;
    public int $is_enabled = 1;
    public string $channels;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_notification_settings');
    }
}
