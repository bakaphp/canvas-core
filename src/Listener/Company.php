<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;
use Canvas\Models\Users;
use Canvas\Models\Companies;
use Canvas\Models\UserConfig;
use Canvas\Models\CompaniesBranches;
use Canvas\Exception\ServerErrorHttpException;
use Baka\Auth\Models\UserCompanyApps;
use Canvas\Models\AppsPlans;
use Canvas\Models\AppsSettings;

class Company
{
    /**
     *  Event to run after a user signs up.
     *
     * @param Event $event
     * @param Users $user
     * @param boolean $isFirstSignup
     * @return void
     */
    public function afterSignup(Event $event, Companies $company): void
    {
        //setup the user notificatoin setting
        $company->set('notifications', $company->user->email);
        $company->set('paid', '1');
        $app = $company->getDI()->getApp();

        //now thta we setup de company and associated with the user we need to setup this as its default company
        if (!UserConfig::findFirst(['conditions' => 'users_id = ?0 and name = ?1', 'bind' => [$company->user->getId(), Companies::DEFAULT_COMPANY]])) {
            $userConfig = new UserConfig();
            $userConfig->users_id = $company->user->getId();
            $userConfig->name = Companies::DEFAULT_COMPANY;
            $userConfig->value = $company->getId();

            $userConfig->saveOrFail();
        }

        $company->associate($company->user, $company);
        $app->associate($company->user, $company);

        /**
         * @var CompaniesBranches
         */
        $branch = new CompaniesBranches();
        $branch->companies_id = $company->getId();
        $branch->users_id = $company->user->getId();
        $branch->name = 'Default';
        $branch->is_default = 1;
        if (!$branch->save()) {
            throw new ServerErrorHttpException((string)current($branch->getMessages()));
        }

        //look for the default plan for this app
        $companyApps = new UserCompanyApps();
        $companyApps->companies_id = $company->getId();
        $companyApps->apps_id = $app->getId();
        //$companyApps->subscriptions_id = 0;

        //we need to assign this company to a plan
        if (empty($company->appPlanId)) {
            $plan = AppsPlans::getDefaultPlan();
            $companyApps->stripe_id = $plan->stripe_id;
        }

        //If the newly created company is not the default then we create a new subscription with the same user
        if (($company->getDI()->getUserData()->default_company != $company->getId()) && $app->subscriptioBased()) {
            $company->set(Companies::PAYMENT_GATEWAY_CUSTOMER_KEY, $company->startFreeTrial());
            $companyApps->subscriptions_id = $company->subscription->getId();
        }

        $companyApps->created_at = date('Y-m-d H:i:s');
        $companyApps->is_deleted = 0;

        if (!$companyApps->save()) {
            throw new ServerErrorHttpException((string)current($companyApps->getMessages()));
        }
    }
}
