<?php

declare(strict_types=1);

namespace Canvas\Contracts;

use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\UnauthorizedException;
use Baka\Support\Str;
use Canvas\Models\Companies;
use Canvas\Models\Roles;
use Canvas\Models\UserRoles;

trait PermissionsTrait
{
    /**
     * Assigned a user this role
     * Example: App.Role.
     *
     * @param string $role
     *
     * @return bool
     */
    public function assignRole(string $role, ?Companies $company = null) : bool
    {
        /**
         * check if we have a dot, that means it legacy and sending the app name
         * not needed any more so we remove it.
         */
        if (Str::contains($role, '.')) {
            $appRole = explode('.', $role);
            $role = $appRole[1];
        }

        $role = Roles::getByName($role);
        $companyId = !is_null($company) ? $company->getId() : $this->currentCompanyId();

        $userRole = UserRoles::findFirstOrCreate([
            'conditions' => 'users_id = ?0 and apps_id = ?1 and companies_id = ?2',
            'bind' => [
                $this->getId(),
                $role->apps_id,
                $companyId
            ]
        ], [
            'users_id' => $this->getId(),
            'roles_id' => $role->getId(),
            'apps_id' => $role->getAppsId(),
            'companies_id' => $companyId,
        ]);

        if ($userRole) {
            $userRole->roles_id = $role->getId();
        }

        return $userRole->saveOrFail();
    }

    /**
     * Assigned a user this role
     * Example: App.Role.
     *
     * @param int $id
     *
     * @return bool
     */
    public function assignRoleById(int $id) : bool
    {
        $role = Roles::getById($id);

        $userRole = UserRoles::findFirstOrCreate([
            'conditions' => 'users_id = :users_id: and apps_id = :apps_id: and companies_id = :companies_id: and is_deleted = 0',
            'bind' => [
                'users_id' => $this->getId(),
                'apps_id' => $role->getAppsId(),
                'companies_id' => $this->currentCompanyId()
            ]], [
                'users_id' => $this->getId(),
                'roles_id' => $role->getId(),
                'apps_id' => $role->getAppsId(),
                'companies_id' => $this->currentCompanyId()
            ]);

        $userRole->roles_id = $role->getId();

        return $userRole->saveOrFail();
    }

    /**
     * Remove a role for the current user
     * Example: App.Role.
     *
     * @param string $role
     *
     * @return bool
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
     * @return bool
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
     * @return bool
     */
    public function can(string $action, bool $throwException = false) : bool
    {
        //we expect the can to have resource.action
        if (!Str::contains($action, '.')) {
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
     * Check whether a role is an Admin or not.
     *
     * @return bool
     */
    public function isAdmin() : bool
    {
        if (!$this->hasRole("{$this->app->name}.Admins")) {
            throw new UnauthorizedException('Current user does not have Admins role');
        }

        return true;
    }
}
