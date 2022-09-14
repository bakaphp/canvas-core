<?php

declare(strict_types=1);

namespace Canvas\Enums;

class NotificationChannels
{
    public const TO_MAIL = 'toMailNotification';
    public const TO_PUSH = 'toSendPushNotification';
    public const TO_REALTIME = 'toPusher';

    public static function getValue(string $notificationChannelSlug){

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
