<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\UnauthorizedException;
use Canvas\Models\Roles;
use Canvas\Models\UserRoles;

/**
 * Trait FractalTrait.
 *
 * @package Canvas\Traits
 */
trait PermissionsTrait
{
    /**
     * Assigned a user this role
     * Example: App.Role.
     *
     * @param string $role
     *
     * @return boolean
     */
    public function assignRole(string $role) : bool
    {
        /**
         * check if we have a dot, that means it legacy and sending the app name
         * not needed any more so we remove it.
         */
        if (strpos($role, '.') !== false) {
            $appRole = explode('.', $role);
            $role = $appRole[1];
        }

        $role = Roles::getByName($role);

        $userRole = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 and apps_id = ?1 and companies_id = ?2',
            'bind' => [
                $this->getId(),
                $role->apps_id,
                $this->currentCompanyId()
            ]
        ]);

        if (!is_object($userRole)) {
            $userRole = new UserRoles();
            $userRole->users_id = $this->getId();
            $userRole->roles_id = $role->getId();
            $userRole->apps_id = $role->apps_id;
            $userRole->companies_id = $this->currentCompanyId();
        } else {
            $userRole->roles_id = $role->getId();
        }

        $userRole->saveOrFail();

        return true;
    }

    /**
     * Assigned a user this role
     * Example: App.Role.
     *
     * @param int $id
     *
     * @return boolean
     */
    public function assignRoleById(int $id) : bool
    {
        $role = Roles::getById($id);

        $userRole = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 and apps_id = ?1 and companies_id = ?2',
            'bind' => [
                $this->getId(),
                $role->apps_id,
                $this->currentCompanyId()
            ]
        ]);

        if (!is_object($userRole)) {
            $userRole = new UserRoles();
            $userRole->users_id = $this->getId();
            $userRole->roles_id = $role->getId();
            $userRole->apps_id = $role->apps_id;
            $userRole->companies_id = $this->currentCompanyId();
        } else {
            $userRole->roles_id = $role->getId();
        }

        $userRole->saveOrFail();

        return true;
    }

    /**
     * Remove a role for the current user
     * Example: App.Role.
     *
     * @param string $role
     *
     * @return boolean
     */
    public function removeRole(string $role) : bool
    {
        $role = Roles::getByAppName($role, $this->getDefaultCompany());

        $userRole = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 and roles_id = ?1 and apps_id = ?2 and companies_id = ?3',
            'bind' => [
                $this->getId(),
                $role->getId(),
                $role->apps_id,
                $this->currentCompanyId()
            ]
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
     *
     * @return boolean
     */
    public function hasRole(string $role) : bool
    {
        $role = Roles::getByAppName($role, $this->getDefaultCompany());

        return (bool) UserRoles::count([
            'conditions' => 'users_id = ?0 and roles_id = ?1 and (apps_id = ?2 or apps_id = ?4) and companies_id = ?3',
            'bind' => [
                $this->getId(),
                $role->getId(),
                $role->apps_id,
                $this->currentCompanyId(),
                $this->di->getApp()->getId()
            ]
        ]);
    }

    /**
     * At this current system / app can you do this?
     *
     * Example: resource.action
     *  Leads.add || leads.updates || lead.delete
     *
     * @param string $action
     * @param bool $throwException
     *
     * @return boolean
     */
    public function can(string $action, bool $throwException = false) : bool
    {
        //if we find the . then les
        if (strpos($action, '.') === false) {
            throw new InternalServerErrorException('ACL - We are expecting the resource for this action');
        }

        $action = explode('.', $action);
        $resource = $action[0];
        $action = $action[1];

        //get your user account role for this app or the canvas ecosystem
        if (!$role = $this->getPermission()) {
            throw new InternalServerErrorException(
                'ACL - User doesn\'t have any set roles in this current app ' . $this->di->getApp()->name
            );
        }

        $canExecute = $this->di->getAcl()->isAllowed($role->roles->name, $resource, $action);

        if ($throwException && !$canExecute) {
            throw new UnauthorizedException("ACL - Users doesn't have permission to run this action `{$action}`");
        }

        return (bool) $canExecute;
    }

    /**
     * Check whether a role is an Admin or not
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        if (!$this->hasRole("{$this->app->name}.Admins")) {
            throw new UnauthorizedException("Current user does not have Admins role");
        }

        return true;
    }
}
