<?php

namespace Canvas\Dto\Notifications;

class UserSettings
{
    public int $id;
    public int $users_id;
    public int $notifications_types_id;
    public string $notification_title;
    public int $is_enabled = 1;
    public array $channels;
}
