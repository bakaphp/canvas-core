<?php

namespace Canvas\Core;

use function function_exists;
use function getenv;

if (!function_exists('Canvas\Core\appPath')) {
    /**
     * Get the application path.
     *
     * @param  string $path
     *
     * @return string
     */
    function appPath(string $path = ''): string
    {
        return dirname(dirname(getcwd())) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('Canvas\Core\envValue')) {
    /**
     * Gets a variable from the environment, returns it properly formatted or the
     * default if it does not exist
     *
     * @param string     $variable
     * @param mixed|null $default
     *
     * @return mixed
     */
    function envValue(string $variable, $default = null)
    {
        $return = $default;
        $value = getenv($variable);
        $values = [
            'false' => false,
            'true' => true,
            'null' => null,
        ];

        if (false !== $value) {
            $return = $values[$value] ?? $value;
        }

        return $return;
    }
}

if (!function_exists('Canvas\Core\appUrl')) {
    /**
     * Constructs a URL for links with resource and id
     *
     * @param string $resource
     * @param int    $recordId
     *
     * @return array|false|mixed|string
     */
    function appUrl(string $resource, int $recordId)
    {
        return sprintf(
            '%s/%s/%s',
            envValue('APP_URL'),
            $resource,
            $recordId
        );
    }
}

if (!function_exists('Canvas\Core\paymentGatewayIsActive')) {
    /**
     * Do we have a payment metho actived on the app?
     *
     * @return boolean
     */
    function paymentGatewayIsActive(): bool
    {
        return !empty(getenv('STRIPE_SECRET')) ? true : false;
    }
}

if (!function_exists('Canvas\Core\isJson')) {
    /**
     * Given a string determine if its a json
     *
     * @param string $string
     * @return boolean
     */
    function isJson(string $string): bool
    {
        json_decode($string);
        return (bool ) (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('Canvas\Core\isSwooleServer')) {
    /**
     * Are we running a Swoole Server for this app?
     *
     * @return boolean
     */
    function isSwooleServer(): bool
    {
        return defined('ENGINE') && ENGINE === 'SWOOLE' ? true : false;
    }
}
