<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Models\Roles;
use Canvas\Models\UserRoles;
use Canvas\Http\Exception\InternalServerErrorException;

/**
 * Trait FractalTrait.
 *
 * @package Canvas\Traits
 */
trait PermissionsTrait
{
    /**
     * Assigne a user this role
     * Example: App.Role.
     *
     * @param string $role
     * @return boolean
     */
    public function assignRole(string $role): bool
    {
        $role = Roles::getByAppName($role, $this->getDefaultCompany());

        if (!is_object($role)) {
            throw new InternalServerErrorException('Role not found in DB');
        }

        $userRole = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 and roles_id = ?1 and apps_id = ?2 and companies_id = ?3',
            'bind' => [$this->getId(), $role->getId(), $role->apps_id, $this->currentCompanyId()]
        ]);

        if (!is_object($userRole)) {
            $userRole = new UserRoles();
            $userRole->users_id = $this->getId();
            $userRole->roles_id = $role->getId();
            $userRole->apps_id = $role->apps_id;
            $userRole->companies_id = $this->currentCompanyId();
            $userRole->saveOrFail();
        }

        return true;
    }

    /**
     * Remove a role for the current user
     * Example: App.Role.
     *
     * @param string $role
     * @return boolean
     */
    public function removeRole(string $role): bool
    {
        $role = Roles::getByAppName($role, $this->getDefaultCompany());

        if (!is_object($role)) {
            throw new InternalServerErrorException('Role not found in DB');
        }

        $userRole = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 and roles_id = ?1 and apps_id = ?2 and companies_id = ?3',
            'bind' => [$this->getId(), $role->getId(), $role->apps_id, $this->currentCompanyId()]
        ]);

        if (is_object($userRole)) {
            return $userRole->delete();
        }

        return false;
    }

    /**
     * Check if the user has this role.
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole(string $role): bool
    {
        $role = Roles::getByAppName($role, $this->getDefaultCompany());

        if (!is_object($role)) {
            throw new InternalServerErrorException('Role not found in DB');
        }

        $userRole = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 and roles_id = ?1 and (apps_id = ?2 or apps_id = ?4) and companies_id = ?3',
            'bind' => [$this->getId(), $role->getId(), $role->apps_id, $this->currentCompanyId(), $this->di->getApp()->getId()]
        ]);

        if (is_object($userRole)) {
            return true;
        }

        return false;
    }

    /**
     * At this current system / app can you do this?
     *
     * Example: resource.action
     *  Leads.add || leads.updates || lead.delete
     *
     * @param string $action
     * @return boolean
     */
    public function can(string $action): bool
    {
        //if we find the . then les
        if (strpos($action, '.') === false) {
            throw new InternalServerErrorException('ACL - We are expecting the resource for this action');
        }

        $action = explode('.', $action);
        $resource = $action[0];
        $action = $action[1];
        //get your user account role for this app or the canvas ecosystem
        //$role = $this->getPermission('apps_id in (' . $this->di->getApp()->getId() . ',' . Roles::DEFAULT_ACL_APP_ID . ')');
        $role = $this->getPermission();

        if (!is_object($role)) {
            throw new InternalServerErrorException('ACL - User doesnt have any set roles in this current app #' . $this->di->getApp()->getId());
        }

        return $this->di->getAcl()->isAllowed($role->roles->name, $resource, $action);
    }
}
