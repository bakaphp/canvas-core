<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\Subscription;
use Canvas\Models\UserCompanyAppsActivities;
use Canvas\Exception\SubscriptionPlanLimitException;
use Canvas\Exception\ServerErrorHttpException;
use ReflectionClass;
use Phalcon\Di;

/**
 * Trait ResponseTrait
 *
 * @package Canvas\Traits
 *
 * @property Users $user
 * @property AppsPlans $appPlan
 * @property CompanyBranches $branches
 * @property Companies $company
 * @property UserCompanyApps $app
 * @property \Phalcon\Di $di
 *
 */
trait SubscriptionPlanLimitTrait
{
    /**
     * Array of routes that can bypass the system when subscription is inactive
     */
    public $bypassRoutes = [
        '/v1/auth/logout'=> ['PUT'],
        '/v1/auth'=> ['POST'],
        '/v1/companies'=> ['GET','POST'],
        '/v1/apps-plans'=> ['PUT','POST'],
    ];

    /**
     * Get the key for the subscriptoin plan limit
     *
     * @return string
     */
    private function getSubcriptionPlanLimitModelKey() : string
    {
        $key = $this->subscriptionPlanLimitKey ?? (new ReflectionClass($this))->getShortName();
        return strtolower($key) . '_total';
    }

    /**
     * Validate if the current module for this app is at the limit of the paid plan
     *
     * @return boolean
     */
    public function isAtLimit() : bool
    {
        if (!Di::getDefault()->has('userData')) {
            return false;
        }

        $subcription = Subscription::getActiveForThisApp();
        $appPlan = $subcription->appPlan;

        if (is_object($appPlan)) {
            //get the current module limit for this plan
            $appPlanLimit = $appPlan->get($this->getSubcriptionPlanLimitModelKey());

            if (!is_null($appPlanLimit)) {
                //get tht total activity of the company current plan
                $currentCompanyAppActivityTotal = UserCompanyAppsActivities::get($this->getSubcriptionPlanLimitModelKey());

                if ($currentCompanyAppActivityTotal >= $appPlanLimit) {
                    throw new SubscriptionPlanLimitException(_($subcription->company->name . ' has reach the limit of it current plan ' . $appPlan->name . ' please upgrade or contact support'));
                }
            }
        }

        return true;
    }

    /**
     * Call at the afterCreate of all modules which are part of a plan activity
     *
     * @return boolean
     */
    public function updateAppActivityLimit() : bool
    {
        if (!Di::getDefault()->has('userData')) {
            return false;
        }

        $companyAppActivityLimit = UserCompanyAppsActivities::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and key = ?2',
            'bind' => [Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getApp()->getId(), $this->getSubcriptionPlanLimitModelKey()]
        ]);

        if (is_object($companyAppActivityLimit)) {
            //its a varchar so lets make sure we convert it to int
            $companyAppActivityLimit->value = (int)$companyAppActivityLimit->value + 1;
            if (!$companyAppActivityLimit->save()) {
                throw new ServerErrorHttpException((string)current($companyAppActivityLimit->getMessages()));
            }
        } else {
            $userCopmanyAppsActivites = new UserCompanyAppsActivities();
            $userCopmanyAppsActivites->set($this->getSubcriptionPlanLimitModelKey(), 1);
        }

        return true;
    }
}
