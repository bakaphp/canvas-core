<?php

namespace Canvas\Cashier;

use Exception;

class Cashier
{
    /**
     * The Cashier library version.
     *
     * @var string
     */
    const VERSION = '12.5.0';

    /**
     * The Stripe API version.
     *
     * @var string
     */
    const STRIPE_VERSION = '2020-03-02';

    /**
     * The current currency.
     *
     * @var string
     */
    protected static string $currency = 'usd';

    /**
     * The current currency symbol.
     *
     * @var string
     */
    protected static string $currencySymbol = '$';

    /**
     * The custom currency formatter.
     *
     * @var callable
     */
    protected static $formatCurrencyUsing;

    /**
     * Set the currency to be used when billing users.
     *
     * @param  string  $currency
     * @param  string|null  $symbol
     *
     * @return void
     */
    public static function useCurrency($currency, $symbol = null)
    {
        static::$currency = $currency;

        static::useCurrencySymbol($symbol ?: static::guessCurrencySymbol($currency));
    }

    /**
     * Guess the currency symbol for the given currency.
     *
     * @param  string  $currency
     *
     * @return string
     */
    protected static function guessCurrencySymbol($currency)
    {
        switch (strtolower($currency)) {
            case 'usd':
            case 'aud':
            case 'cad':
                return '$';
            case 'eur':
                return '€';
            case 'gbp':
                return '£';
            default:
                throw new Exception('Unable to guess symbol for currency. Please explicitly specify it.');
        }
    }

    /**
     * Get the currency currently in use.
     *
     * @return string
     */
    public static function usesCurrency()
    {
        return static::$currency;
    }

    /**
     * Set the currency symbol to be used when formatting currency.
     *
     * @param  string  $symbol
     *
     * @return void
     */
    public static function useCurrencySymbol($symbol)
    {
        static::$currencySymbol = $symbol;
    }

    /**
     * Get the currency symbol currently in use.
     *
     * @return string
     */
    public static function usesCurrencySymbol()
    {
        return static::$currencySymbol;
    }

    /**
     * Set the custom currency formatter.
     *
     * @param  callable  $callback
     *
     * @return void
     */
    public static function formatCurrencyUsing(callable $callback)
    {
        static::$formatCurrencyUsing = $callback;
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     *
     * @return string
     */
    public static function formatAmount($amount)
    {
        if (static::$formatCurrencyUsing) {
            return call_user_func(static::$formatCurrencyUsing, $amount);
        }

        $amount = number_format($amount / 100, 2);

        if (static::startsWith($amount, '-')) {
            return '-' . static::usesCurrencySymbol() . ltrim($amount, '-');
        }

        return static::usesCurrencySymbol() . $amount;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * Get the default Stripe API options.
     *
     * @param  array  $options
     *
     * @return array
     */
    public static function stripeOptions(array $options = []) : array
    {
        return array_merge([
            'api_key' => getenv('STRIPE_SECRET'),
            'stripe_version' => static::STRIPE_VERSION,
        ], $options);
    }
}
