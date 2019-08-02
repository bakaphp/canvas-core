<?php

declare(strict_types=1);

namespace Canvas\Listener;

use Phalcon\Events\Event;
use Canvas\Models\Users;
use Canvas\Models\Companies;
use Canvas\Models\UserRoles;
use Canvas\Auth\App;
use Canvas\Models\Roles;
use Canvas\Models\UsersInvite;

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
            $company->name = !empty($user->defaultCompanyName) ? $user->defaultCompanyName : $user->displayname.'CP';
            $company->users_id = $user->getId();

            $company->saveOrFail();

            $user->default_company = $company->getId();

            $user->updateOrFail();

            if (empty($user->stripe_id)) {
                $user->stripe_id = $company->getPaymentGatewayCustomerId();
                $user->default_company_branch = $user->getDefaultCompany()->branch->getId();
                $user->updateOrFail();
            }

        } else {
            //we have the company id
            if (empty($user->default_company_branch)) {
                $user->default_company_branch = $user->getDefaultCompany()->branch->getId();
            }

            $user->getDI()->getApp()->associate($user, $user->getDefaultCompany());
        }

        //Create new company associated company
        $user->getDefaultCompany()->associate($user, $user->getDefaultCompany());
        
        //Insert record into user_roles
        $userRole = new UserRoles();
        $userRole->users_id = $user->id;
        $userRole->roles_id = $user->roles_id;
        $userRole->apps_id = $user->getDI()->getApp()->getId();
        $userRole->companies_id = $user->getDefaultCompany()->getId();

        if (!$userRole->save()) {
            throw new ServerErrorHttpException((string)current($userRole->getMessages()));
        }
    }

    /**
     * Events after a user is invited to the system
     *
     * @param Event $event
     * @param Users $user
     * @return void
     */
    public function afterInvite(Event $event, Users $user, UsersInvite $usersInvite)
    {
        //assign default company
        if (!$user->get(Companies::cacheKey())) {
            $user->set(Companies::cacheKey(), $usersInvite->company->getId());
        }

        //if its a ecosystem app and we are inviting a user to it, we need to move the user password to it
        if ($user->getDI()->getApp()->ecosystemAuth()) {
            App::updatePassword($user, $user->password);
        }

        Roles::assignDefault($user);
    }
}
