<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Baka\Auth\Models\UserCompanyApps;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesAssociations;
use Canvas\Models\CompaniesBranches;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Roles;
use Canvas\Models\Users;
use Phalcon\Di;
use Phalcon\Events\Event;

class Company
{
    /**
     *  Event to run after a user signs up.
     *
     * @param Event $event
     * @param Users $user
     * @param bool $isFirstSignup
     *
     * @return void
     */
    public function afterSignup(Event $event, Companies $company) : void
    {
        $app = Di::getDefault()->get('app');

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
        $branch->name = $company->name;
        $branch->is_default = 1;
        $branch->saveOrFail();

        //look for the default plan for this app
        $companyApps = new UserCompanyApps();
        $companyApps->companies_id = $company->getId();
        $companyApps->apps_id = $app->getId();
        $companyApps->created_at = date('Y-m-d H:i:s');
        $companyApps->is_deleted = 0;
        $companyApps->saveOrFail();

        $companiesGroup = CompaniesGroups::findFirstOrCreate([
            'conditions' => 'apps_id = ?0 and users_id = ?1 and is_deleted = 0',
            'bind' => [
                Di::getDefault()->get('app')->getId(),
                Di::getDefault()->get('userData')->getId()
            ]
        ], [
            'name' => $company->name,
            'apps_id' => Di::getDefault()->get('app')->getId(),
            'users_id' => Di::getDefault()->get('userData')->getId(),
        ]);

        //if the app is subscription based, create a free trial for this companyGroup and this app
        if ($app->subscriptionBased()) {
            $companiesGroup->startFreeTrial();
        }

        /**
         * Let's associate companies and companies_groups.
         */
        $companiesAssoc = new CompaniesAssociations();
        $companiesAssoc->companies_id = $company->getId();
        $companiesAssoc->companies_groups_id = $companiesGroup->getId();
        $companiesAssoc->saveOrFail();

        //assign role
        $company->user->assignRole(Roles::DEFAULT);
    }
}
