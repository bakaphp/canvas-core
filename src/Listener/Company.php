<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;
use Canvas\Models\Users;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesBranches;
use Baka\Auth\Models\UserCompanyApps;
use Canvas\Http\Exception\InternalServerErrorException;
use Canvas\Models\AppsPlans;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\CompaniesAssociations;
use Phalcon\Di;

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
        /**
         * @todo removed , we have it on suscription
         */
        $company->set('paid', '1');
        $app = $company->getDI()->getApp();

        //now that we setup de company and associated with the user we need to setup this as its default company
        if (!$company->user->get(Companies::cacheKey())) {
            $company->user->set(Companies::cacheKey(), $company->getId());
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
        $branch->saveOrFail();

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

        //if the app is subscriptoin based, create a free trial for this company
        if ($app->subscriptioBased()) {
            $company->set(
                Companies::PAYMENT_GATEWAY_CUSTOMER_KEY,
                $company->startFreeTrial()
            );

            $companyApps->subscriptions_id = $company->subscription->getId();
        }

        $companyApps->created_at = date('Y-m-d H:i:s');
        $companyApps->is_deleted = 0;

        if (!$companyApps->save()) {
            throw new InternalServerErrorException((string)current($companyApps->getMessages()));
        }

        $companiesGroup = CompaniesGroups::findFirst([
            'conditions' => 'apps_id = ?0 and users_id = ?1 and is_deleted = 0',
            'bind' => [Di::getDefault()->getApp()->getId(), Di::getDefault()->getUserData()->getId()]
        ]);

        if (!$companiesGroup) {
            $companiesGroup = new CompaniesGroups();
            $companiesGroup->name = $company->name;
            $companiesGroup->apps_id = Di::getDefault()->getApp()->getId();
            $companiesGroup->users_id = Di::getDefault()->getUserData()->getId();
            $companiesGroup->saveOrFail();
        }

        /**
         * Let's associate companies and companies_groups.
         */
        $companiesAssoc = new CompaniesAssociations();
        $companiesAssoc->companies_id = $company->id;
        $companiesAssoc->companies_groups_id = $companiesGroup->id;
        $companiesAssoc->saveOrFail();
    }
}
