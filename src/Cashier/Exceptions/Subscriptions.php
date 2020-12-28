<?php

namespace Canvas\Cashier\Exceptions;

use Canvas\Exception\Exception;
use Canvas\Models\AppsPlans;
use Phalcon\Mvc\ModelInterface;

class Subscriptions extends Exception
{
    /**
     * Is not a valid customer.
     *
     * @param ModelInterface $model
     *
     * @return self
     */
    public static function duplicatePlan(AppsPlans $plan) : self
    {
        return new static('This Plan' . $plan->name . ' is already attached to this subscription');
    }
}
