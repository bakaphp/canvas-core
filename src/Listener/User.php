<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Canvas\Auth\App;
use Canvas\Models\Apps;
use Canvas\Models\Companies;
use Canvas\Models\Roles;
use Canvas\Models\Users;
use Canvas\Models\UsersInvite;
use Phalcon\Events\Event;

class User
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
    public function afterSignup(Event $event, Users $user, bool $isFirstSignup) : void
    {
        /**
         * User signing up for a new app / plan
         * How do we know? well he doesn't have a default_company.
         */
        if ($isFirstSignup) {
            /**
             * Let's create a new Companies.
             */
            $company = new Companies();
            //for signup that don't send a company name
            $company->name = !empty($user->defaultCompanyName) ? $user->defaultCompanyName : $user->displayname . 'CP';
            $company->users_id = $user->getId();
            $company->saveOrFail();

            $user->default_company = $company->getId();
            $user->updateOrFail();

            if (empty($user->stripe_id)) {
                $user->stripe_id = $company->getPaymentGatewayCustomerId();
                $user->default_company_branch = $user->getDefaultCompany()->branch->getId();
                $user->updateOrFail();
            }

            //Set default company and default company branch
            $user->set(Companies::cacheKey(), $company->getId());
            $user->set($company->branchCacheKey(), $company->branch->getId());
        } else {
            //Get user's default company info
            $company = Companies::findFirstOrFail($user->default_company);
            $user->set(Companies::cacheKey(), $company->getId());
            $user->set($company->branchCacheKey(), $company->branch->getId());

            $user->getDI()->get('app')->associate($user, $user->getDefaultCompany());
        }

        //Create new company associated company
        $user->getDefaultCompany()->associate($user, $user->getDefaultCompany());

        //Insert record into user_roles
        if (!$role = $user->getDI()->get('app')->get(Apps::APP_DEFAULT_ROLE_SETTING)) {
            $role = $user->getDI()->get('app')->name . '.' . Roles::getById((int) $user->roles_id)->name;
        }

        //assign default location
        if (!$defaultCountryId = $user->getDI()->get('app')->get(Apps::APP_DEFAULT_COUNTRY)) {
            $user->country_id = $defaultCountryId;
            $user->updateOrFail();
        }

        $user->assignRole($role);
    }

    /**
     * Events after a user is invited to the system.
     *
     * @param Event $event
     * @param Users $user
     *
     * @return void
     */
    public function afterInvite(Event $event, Users $user, UsersInvite $usersInvite)
    {
        //assign default company
        if (!$user->get(Companies::cacheKey())) {
            $user->set(Companies::cacheKey(), $usersInvite->company->getId());
        }

        //if its a ecosystem app and we are inviting a user to it, we need to move the user password to it
        if (!$user->getDI()->getApp()->ecosystemAuth()) {
            App::updatePassword($user, $user->password);
        }

        /**
         * @todo this is hackable , need to add use Roles::getById($usersInvite->role_id) , without
         * but we need the company info without been logged in
         */
        $user->assignRole(
            Roles::findFirstOrFail($usersInvite->role_id)->name,
            $usersInvite->company
        );
    }

    /**
     * Delete user.
     *
     * @param Event $event
     * @param Users $user
     *
     * @return void
     */
    public function afterDelete(Event $event, Users $user) : void
    {
        $user->sessions->delete();
        $user->sessionKeys->delete();
        $user->sources->delete();
        $user->config->delete();
    }
}
