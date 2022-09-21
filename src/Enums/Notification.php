<?php

declare(strict_types=1);

namespace Canvas\Enums;

class Notification
{
    public const USER_MUTE_ALL_MAIL_STATUS = 'notification_mute_all_mail_status';
    public const USER_MUTE_ALL_PUSH_STATUS = 'notification_mute_all_push_status';
    public const USER_MUTE_ALL_REALTIME_STATUS = 'notification_mute_all_realtime_status';
    public const USER_MUTE_ALL_STATUS = 'notification_mute_all_status';


    /**
     * Return notification mute status constant by notification type slug
     * 
     * @param string $slug
     * 
     * @return string
     */
    public static function getValueBySlug(string $slug) : string
    {
        switch ($slug) {
            case 'email':
                return self::USER_MUTE_ALL_MAIL_STATUS;
            case 'push':
                return self::USER_MUTE_ALL_PUSH_STATUS;
            case 'realtime':
                return self::USER_MUTE_ALL_REALTIME_STATUS;
            default:
                return self::USER_MUTE_ALL_STATUS;
        }
    }
}
