
<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Auth\UserInterface;
use Canvas\Notifications\UserInactiveConfirmation;
use Phalcon\Di;

class UsersAssociatedApps extends AbstractModel implements UserInterface
{
    public int $users_id;
    public int $apps_id;
    public int $companies_id;
    public string $identify_id;
    public int $user_active;
    public string $user_role;
    public ?string $password = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->setSource('users_associated_apps');
    }

    /**
     * Upload Files.
     *
     * @todo move this to the baka class
     *
     * @return void
     */
    public function afterSave()
    {
        if (!$this->validateIsActive()) {
            $parentUser = Di::getDefault()->getUserData();
            $userInactiveConfirmation = new UserInactiveConfirmation($parentUser);
            $userInactiveConfirmation->setFrom($parentUser);

            $this->getUser()->notify($userInactiveConfirmation);
        }
    }

    /**
     * Checks whether or not a user is active on the current app's company.
     *
     * @return bool
     */
    public function validateIsActive() : bool
    {
        return $this->user_active ? true : false;
    }

    /**
     * Get record by user's id.
     *
     * @param int $userId
     *
     * @return UsersAssociatedApps
     */
    public static function getByUserId(int $userId) : self
    {
        return self::findFirstOrFail([
            'conditions' => 'apps_id = :apps_id: and users_id = :users_id: and companies_id = :companies_id: and is_deleted = 0',
            'bind' => [
                'apps_id' => Di::getDefault()->get('app')->getId(),
                'users_id' => $userId,
                'companies_id' => Di::getDefault()->get('userData')->get(Companies::cacheKey())
            ]
        ]);
    }

     /**
     * Desassociated a user from an app
     * 
     * @param Users $users
     * @param Users $companies
     * 
     * @return void
     */
    public static function disassociateUserFromApp(Users $users, Companies $companies) : void
    {
        $userAssociatedApp = UsersAssociatedApps::findFirstOrFail([
            "conditions" => "users_id = :users_id: and companies_id = :companies_id: and user_active = 1 and is_deleted = 0",
            "bind" => [
                "users_id" => $users->getId(),
                "companies_id" => $companies->getId()
            ]
        ]);

        $userAssociatedApp->is_deleted = 1;
        $userAssociatedApp->saveOrFail();
    }
}
