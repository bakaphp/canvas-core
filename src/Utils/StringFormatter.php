<?php

declare(strict_types=1);

namespace Canvas\Utils;

/**
 * New event Manager to allow use to use fireToQueue.
 */
class StringFormatter
{
    /**
     * Return a sanitized version of a phone number.
     *
     * @param null|string $phone
     *
     * @return string
     */
    public static function sanitizePhoneNumber(?string $phone) : string
    {
        return $phone ? preg_replace('/\D+/', '', $phone) : '';
    }
}
