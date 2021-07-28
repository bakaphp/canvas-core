<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Canvas\Models\Companies;
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

        //Set Default Company if record is not found
        if (!$company->user->get(Companies::cacheKey())) {
            $company->user->set(Companies::cacheKey(), $company->getId());
        }

        $company->associate($company->user, $company);
        $app->associate($company->user, $company);

        //create default branch
        $company->createBranch();

        //Set Default Company Branch if record is not found
        if (!$company->user->get($company->branchCacheKey())) {
            $company->user->set($company->branchCacheKey(), $company->branch->getId());
        }

        //look for the default plan for this app
        $company->registerInApp($app);

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
            'is_default' => 1
        ]);

        /**
         * Let's associate companies and companies_groups.
         */
        $companiesGroup->associate($company);

        /**
         * only assign a role to the user within the company if its not a new signup
         * but the creation of a new company to a already user of the app.
         */
        if (!$company->user->isFirstSignup()) {
            $company->user->assignRole(Roles::DEFAULT, $company);
        }

        //if the app is subscription based, create a free trial for this companyGroup and this app
        if ($app->subscriptionBased()) {
            $companiesGroup->startFreeTrial();
        }
    }
}
