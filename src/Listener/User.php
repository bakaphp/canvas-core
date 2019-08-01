<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;
use Canvas\Models\Users;
use Canvas\Models\Companies;
use Canvas\Models\UserRoles;
use Canvas\Auth\App;

class User
{
    /**
     *  Event to run after a user signs up.
     *
     * @param Event $event
     * @param Users $user
     * @param boolean $isFirstSignup
     * @return void
     */
    public function afterSignup(Event $event, Users $user, bool $isFirstSignup): void
    {
        /**
        * User signing up for a new app / plan
        * How do we know? well he doesnt have a default_company.
        */
        if ($isFirstSignup) {
            $company = new Companies();
            //for signups that dont send a company name
            $company->name = empty($user->defaultCompanyName) ? $user->defaultCompanyName : $user->displayname.'CP';
            $company->users_id = $user->getId();

            $company->saveOrFail();

            $user->default_company = $company->getId();

            $user->updateOrFail();

            $user->stripe_id = $company->getPaymentGatewayCustomerId();
            $user->default_company_branch = $user->defaultCompany->branch->getId();
            $user->updateOrFail();

        //update default subscription free trial
            //$company->app->subscriptions_id = $user->startFreeTrial()->getId();
            //$company->update();
        } else {
            //we have the company id
            if (empty($user->default_company_branch)) {
                $user->default_company_branch = $user->defaultCompany->branch->getId();
            }

            $user->getDI()->getApp()->associate($user, $user->defaultCompany);
        }

        //Create new company associated company
        $user->defaultCompany->associate($user, $user->defaultCompany);
        
        //Insert record into user_roles
        $userRole = new UserRoles();
        $userRole->users_id = $user->id;
        $userRole->roles_id = $user->roles_id;
        $userRole->apps_id = $user->getDI()->getApp()->getId();
        $userRole->companies_id = $user->default_company;

        if (!$userRole->save()) {
            throw new ServerErrorHttpException((string)current($userRole->getMessages()));
        }
    }

    /**
     * After inviting a user
     *
     * @param Event $event
     * @param Users $user
     * @return void
     */
    public function afterInvite(Event $event, Users $user)
    {
        //if its a ecosystem app and we are inviting a user to it, we need to move the user password to it
        if ($user->getDI()->getApp()->ecosystemAuth()) {
            App::updatePassword($user, $user->password);
        }
    }
}
