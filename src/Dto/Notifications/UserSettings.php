<?php

namespace Canvas\Dto\Notifications;

class UserSettings
{
    public int $users_id;
    public string $name;
    public ?string $description = null;
    public int $notifications_types_id;
    public int $is_enabled = 1;
    public array $channels;
}
