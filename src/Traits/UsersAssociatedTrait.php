<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\Users;
use Canvas\Models\Roles;
use Canvas\Models\Companies;
use Exception;

/**
 * Trait ResponseTrait.
 *
 * @package Canvas\Traits
 *
 * @property Users $user
 * @property Config $config
 * @property Request $request
 * @property Auth $auth
 * @property \Phalcon\Di $di
 * @property Roles $roles_id
 *
 */
trait UsersAssociatedTrait
{
    /**
     * create new related User Associated instance dynamicly.
     * @param Users $user
     * @param Companies $company
     * @return void
     * @todo Find a better way to handle namespaces for models
     */
    public function associate(Users $user, Companies $company): array
    {
        $class = str_replace('UsersAssociated\\', 'UsersAssociated', substr_replace(get_class($this), '\UsersAssociated', strrpos(get_class($this), '\\'), 0));
        $usersAssociatedModel = new $class();
        $usersAssociatedModel->users_id = $user->getId();
        $usersAssociatedModel->companies_id = $company->getId();
        $usersAssociatedModel->apps_id = $this->di->getApp()->getId();
        $usersAssociatedModel->identify_id = $user->getId();
        $usersAssociatedModel->user_active = 1;
        $usersAssociatedModel->user_role = $user->roles_id;
        $usersAssociatedModel->created_at = date('Y-m-d H:i:s');

        $usersAssociatedModel->saveOrFail();

        return $usersAssociatedModel->toArray();
    }
}
