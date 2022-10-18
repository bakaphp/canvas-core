<?php

declare(strict_types=1);

namespace Canvas\Enums;

class NotificationChannels
{
    public const MAIL = 1;
    public const PUSH = 2;
    public const REALTIME = 3;

    /**
     * Get the value of the enum by slug
     * 
     * @param string $notificationChannelSlug
     * 
     * @return int
     */
    public static function getValueBySlug(string $notificationChannelSlug) : int
    {

        switch ($notificationChannelSlug) {
            case 'email':
                return self::MAIL;
            case 'push':
                return self::PUSH;
            case 'realtime':
                return self::REALTIME;
            default:
                break;
        }
    }
}
