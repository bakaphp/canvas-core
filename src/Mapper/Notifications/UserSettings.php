<?php

declare(strict_types=1);

namespace Canvas\Mapper\Notifications;

use AutoMapperPlus\CustomMapper\CustomMapper;

use function Baka\isJson;

class UserSettings extends CustomMapper
{

    /**
     * Map.
     *
     * @param \Canvas\Models\Notifications\UserSettings $userSettings
     * @param \Canvas\Dto\Notifications\UserSettings $userSettingsDto
     * @param array $context
     *
     * @return mixed
     */
    public function mapToObject($userSettings, $userSettingsDto, array $context = [])
    {
        $userSettingsDto->id = $userSettings->getId();
        $userSettingsDto->users_id = $userSettings->users_id;
        $userSettingsDto->notifications_types_id = $userSettings->notifications_types_id;
        $userSettingsDto->notification_title = $userSettings->notification->name;
        $userSettingsDto->is_enabled = $userSettings->is_enabled;
        $userSettingsDto->channels = !empty($userSettings->channels) && isJson($userSettings->channels) ? json_decode($userSettings->channels, true) : [];

        return $userSettingsDto;
    }
}
