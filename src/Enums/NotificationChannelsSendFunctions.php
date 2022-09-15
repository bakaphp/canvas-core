<?php

declare(strict_types=1);

namespace Canvas\Enums;

class NotificationChannelsSendFunctions
{
    public const TO_MAIL = 'toMailNotification';
    public const TO_PUSH = 'toSendPushNotification';
    public const TO_REALTIME = 'toPusher';

    /**
     * Get the value of the enum by slug
     * 
     * @param string $notificationChannelSlug
     * 
     * @return string
     */
    public static function getValueBySlug(string $notificationChannelSlug) : string
    {

        switch ($notificationChannelSlug) {
            case 'email':
                return self::TO_MAIL;
            case 'push':
                return self::TO_PUSH;
            case 'realtime':
                return self::TO_REALTIME;
            default:
                break;
        }
    }
}
