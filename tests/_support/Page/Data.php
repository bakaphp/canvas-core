<?php

namespace Page;

class Data
{
    public static string $loginUrl = '/v1/auth';
    public static string $usersUrl = '/v1/users';
    public static string $statusUrl = '/v1/status';
    public static string $defaultEmail = 'tes2t@baka.io';
    public static string $defaultPassword = 'bakatest123567';

    /**
     * @return array
     */
    public static function loginJsonDefaultUser()
    {
        return [
            'email' => 'nobody@baka.io',
            'password' => self::$defaultPassword,
        ];
    }

    /**
     * @return array
     */
    public static function loginJson()
    {
        return [
            'email' => self::$defaultEmail,
            'password' => self::$defaultPassword,
        ];
    }
}
