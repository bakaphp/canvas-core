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
     * @param string $phone
     *
     * @return string
     */
    public static function sanitizePhoneNumber(string $phone) : string
    {
        return preg_replace('/\D+/', '', $phone);
    }
}
