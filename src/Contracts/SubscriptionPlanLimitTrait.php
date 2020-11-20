<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use function Baka\getShortClassName;
use Canvas\Exception\SubscriptionPlanLimitException;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Models\Subscription;
use Canvas\Models\UserCompanyAppsActivities;
use Phalcon\Di;

trait SubscriptionPlanLimitTrait
{
    /**
     * Get the key for the subscription plan limit.
     *
     * @return string
     */
    private function getSubscriptionPlanLimitModelKey() : string
    {
        $key = $this->subscriptionPlanLimitKey ?? getShortClassName($this);
        return strtolower($key) . '_total';
    }

    /**
     * Validate if the current module for this app is at the limit of the paid plan.
     *
     * @throws SubscriptionPlanLimitException
     *
     * @return boolean
     */
    public function isAtLimit() : bool
    {
        if (!Di::getDefault()->has('userData')) {
            return false;
        }

        //if its not a subscription based app top this
        if (!Di::getDefault()->get('app')->subscriptionBased()) {
            return false;
        }

        $subscription = Subscription::getActiveForThisApp();
        $appPlan = $subscription->appPlan;

        if (is_object($appPlan)) {
            //get the current module limit for this plan
            $appPlanLimit = $appPlan->get($this->getSubscriptionPlanLimitModelKey());

            if (!is_null($appPlanLimit)) {
                //get tht total activity of the company current plan
                $currentCompanyAppActivityTotal = UserCompanyAppsActivities::get($this->getSubscriptionPlanLimitModelKey());

                if ($currentCompanyAppActivityTotal >= $appPlanLimit) {
                    throw new SubscriptionPlanLimitException(_(
                        'This action cannot be performed ' . $subscription->company->name . ' has reach the limit of it current plan ' . $appPlan->name . ' please upgrade or contact support'
                    ));
                }
            }
        }

        return true;
    }

    /**
     * Call at the afterCreate of all modules which are part of a plan activity.
     *
     * @throws InternalServerErrorException
     *
     * @return boolean
     */
    public function updateAppActivityLimit() : bool
    {
        if (!Di::getDefault()->has('userData')) {
            return false;
        }

        //if its not a subscription based app top this
        if (!Di::getDefault()->get('app')->subscriptionBased()) {
            return false;
        }

        $companyAppActivityLimit = UserCompanyAppsActivities::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and key = ?2',
            'bind' => [
                Di::getDefault()->get('userData')->currentCompanyId(),
                Di::getDefault()->get('app')->getId(),
                $this->getSubscriptionPlanLimitModelKey()
            ]
        ]);

        if (is_object($companyAppActivityLimit)) {
            //its a varchar so lets make sure we convert it to int
            $companyAppActivityLimit->value = (int)$companyAppActivityLimit->value + 1;
            $companyAppActivityLimit->saveOrFail();
        } else {
            $userCompanyAppsActivities = new UserCompanyAppsActivities();
            $userCompanyAppsActivities->set($this->getSubscriptionPlanLimitModelKey(), 1);
        }

        return true;
    }
}
