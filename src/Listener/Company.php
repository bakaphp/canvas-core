<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Canvas\Models\Companies;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\Notifications;
use Canvas\Models\Roles;
use Canvas\Models\Subscription;
use Canvas\Models\Users;
use Canvas\Models\UsersAssociatedCompanies;
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
        $branch = $company->createBranch();

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
        if ($app->usesSubscriptions()) {
            $companiesGroup->startFreeTrial(
                $companiesGroup,
                $company,
                $branch
            );
        }
    }

    /**
     * After delete a company.
     *
     * @param Event $event
     * @param Companies $company
     *
     * @return void
     */
    public function afterDelete(Event $event, Companies $company) : void
    {
        $verifyUsers = [];
        $users = $company->UsersAssociatedCompanies;

        foreach ($users as $user) {
            $verifyUsers[$user->users_id] = $user->users_id;
            $user->delete();
        }

        $users = $company->UsersAssociatedApps;

        if ($users->count()) {
            $users->delete();
        }

        $userInvite = $company->userInvites;

        if ($userInvite->count()) {
            $userInvite->delete();
        }

        $userCompaniesApps = $company->app;

        if ($userCompaniesApps->count()) {
            $userCompaniesApps->delete();
        }

        $userCompaniesAppsActivities = $company->appActivities;

        if ($userCompaniesAppsActivities->count()) {
            $userCompaniesAppsActivities->delete();
        }

        $company->settings->delete();
        $company->branches->delete();
        $company->companiesAssoc->delete();

        $notifications = Notifications::find([
            'conditions' => 'companies_id = :companies_id:',
            'bind' => [
                'companies_id' => $company->getId()
            ]
        ]);

        if ($notifications->count()) {
            $notifications->delete();
        }

        $notificationUnsubscribe = $company->notifications;

        if ($notificationUnsubscribe->count()) {
            $notificationUnsubscribe->delete();
        }

        $roles = $company->roles;

        if ($roles->count()) {
            $roles->delete();
        }

        $subscriptions = Subscription::find([
            'conditions' => 'companies_id = :companies_id:',
            'bind' => [
                'companies_id' => $company->getId()
            ]
        ]);

        foreach ($subscriptions as $subscription) {
            $subscription->plans->delete();
            $subscription->delete();
        }

        $useRoles = $company->userRoles;

        if ($useRoles->count()) {
            $useRoles->delete();
        }

        $userWebHooks = $company->webhooks;

        if ($userWebHooks->count()) {
            $userWebHooks->delete();
        }

        //remove estranged users from the system
        foreach ($verifyUsers as $userId) {
            $user = Users::findFirstById($userId);

            $hasCompany = UsersAssociatedCompanies::count([
                'conditions' => 'users_id = :users_id:',
                'bind' => [
                    'users_id' => $user->getId()
                ]
            ]);

            if ($user && !$hasCompany) {
                $user->delete();
            }
        }
    }
}
