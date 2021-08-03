<?php

namespace Canvas\Cashier\Exceptions;

use function Baka\getShortClassName;
use Canvas\Exception\Exception;
use Phalcon\Mvc\ModelInterface;

class Customers extends Exception
{
    /**
     * Is not a valid customer.
     *
     * @param ModelInterface $model
     *
     * @return self
     */
    public static function notYetCreated(ModelInterface $model) : self
    {
        return new static(getShortClassName($model) . ' is not a Stripe customer yet. Use method createAsStripeCustomer first.');
    }

    /**
     * Already exist.
     *
     * @param ModelInterface $model
     *
     * @return self
     */
    public static function exists(ModelInterface $model) : self
    {
        return new static(getShortClassName($model) . ' is already a Stripe customer with ID ' . $owner->stripe_id);
    }
}
