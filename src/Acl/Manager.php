<?php

declare(strict_types=1);

namespace Canvas\Acl;

use BadMethodCallException;
use Baka\Support\Str;
use Canvas\Models\AccessList as AccessListDB;
use Canvas\Models\Apps;
use Canvas\Models\Companies;
use Canvas\Models\Resources as ResourcesDB;
use Canvas\Models\ResourcesAccesses;
use Canvas\Models\Roles as RolesDB;
use Phalcon\Acl\Adapter\AbstractAdapter;
use Phalcon\Acl\Component;
use Phalcon\Acl\Enum as AclEnum;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Role;
use Phalcon\Acl\RoleInterface;
use Phalcon\Db\AdapterInterface as DbAdapter;
use Phalcon\Db\Enum;
use Phalcon\Di;

class Manager extends AbstractAdapter
{
    /**
     * @var DbAdapter
     */
    protected $connection;

    /**
     * Roles table.
     *
     * @var string
     */
    protected $roles;

    /**
     * Resources table.
     *
     * @var string
     */
    protected $resources;

    /**
     * Resources Accesses table.
     *
     * @var string
     */
    protected $resourcesAccesses;

    /**
     * Access List table.
     *
     * @var string
     */
    protected $accessList;

    /**
     * Roles Inherits table.
     *
     * @var string
     */
    protected $rolesInherits;

    /**
     * Default action for no arguments is allow.
     *
     * @var int
     */
    protected $noArgumentsDefaultAction = AclEnum::ALLOW;

    /**
     * Company Object.
     *
     * @var Companies
     */
    protected $company;

    /**
     * App Objc.
     *
     * @var Apps
     */
    protected $app;

    /**
     * Class constructor.
     *
     * @param  array $options Adapter config
     *
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $this->connection = $options['db'];
    }

    /**
     * Set current user Company.
     *
     * @param Companies $company
     *
     * @return void
     */
    public function setCompany(Companies $company) : void
    {
        $this->company = $company;
    }

    /**
     * Set current user app.
     *
     * @param Apps $app
     *
     * @return void
     */
    public function setApp(Apps $app) : void
    {
        $this->app = $app;
    }

    /**
     * Get the current App.
     *
     * @return Apps
     */
    public function getApp() : Apps
    {
        if (!is_object($this->app)) {
            $this->app = Di::getDefault()->get('app');
        }

        return $this->app;
    }

    /**
     * Get the current App.
     *
     * @return Companies
     */
    public function getCompany() : Companies
    {
        if (!is_object($this->company)) {
            if (!Di::getDefault()->has('userData')) {
                $this->company = new Companies();
                $this->company->id = 1;
                $this->company->name = 'Canvas';
            } else {
                $this->company = Di::getDefault()->get('userData')->getDefaultCompany();
            }
        }

        return $this->company;
    }

    /**
     * {@inheritdoc}
     *
     * Example:
     * <code>
     * $acl->addRole(new Phalcon\Acl\Role('administrator'), 'consulter');
     * $acl->addRole('administrator', 'consulter');
     * </code>
     *
     * @param  \Phalcon\Acl\Role|string $role
     * @param  int   $scope
     * @param  string                   $accessInherits
     *
     * @return bool
     *
     * @throws \Phalcon\Acl\Exception
     */
    public function addRole($role, $scope = 0, $accessInherits = null) : bool
    {
        if (is_string($role)) {
            $role = $this->setAppByRole($role);

            $role = new Role($role, ucwords($role) . ' Role');
        }

        if (!$role instanceof RoleInterface) {
            throw new Exception('Role must be either an string or implement RoleInterface');
        }

        if (!RolesDB::exist($role)) {
            $rolesDB = new RolesDB();
            $rolesDB->name = $role->getName();
            $rolesDB->description = $role->getDescription() ?: $role->getName();
            $rolesDB->companies_id = $this->getCompany()->getId();
            $rolesDB->apps_id = $this->getApp()->getId();
            $rolesDB->scope = $scope;
            $rolesDB->saveOrFail();

            $accessListDB = new AccessListDB();
            $accessListDB->roles_name = $role->getName();
            $accessListDB->roles_id = $rolesDB->getId();
            $accessListDB->resources_name = '*';
            $accessListDB->access_name = '*';
            $accessListDB->allowed = $this->noArgumentsDefaultAction;
            $accessListDB->apps_id = $this->getApp()->getId();
            $accessListDB->saveOrFail();
        }

        if ($accessInherits) {
            return $this->addInherit($role->getName(), $accessInherits);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $roleName
     *
     * @return bool
     */
    public function isRole($roleName) : bool
    {
        return RolesDB::isRole($roleName);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $resourceName
     *
     * @return bool
     */
    public function isComponent($resourceName) : bool
    {
        return ResourcesDB::isResource($resourceName);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $roleName
     * @param  string $roleToInherit
     *
     * @throws \Phalcon\Acl\Exception
     */
    public function addInherit($roleName, $roleToInherit) : bool
    {
        return RolesDB::addInherit($roleName, $roleToInherit);
    }

    /**
     * Given a resource with a dot CRM.Leads , it will set the app.
     *
     * @param string $resource
     *
     * @return string
     */
    protected function setAppByResource(string $resource) : string
    {
        //check if we have a dot , meaning we are sending the specific app to use
        if (Str::contains($resource, '.')) {
            $appResource = explode('.', $resource);
            $resource = $appResource[1];
            $appName = $appResource[0];

            //look for the app and set it
            if ($app = Apps::getACLApp($appName)) {
                $this->setApp($app);
            }
        }

        return $resource;
    }

    /**
     * Given a resource with a dot CRM.Leads , it will set the app.
     *
     * @param string $resource
     *
     * @return string
     */
    protected function setAppByRole(string $role) : string
    {
        //check if we have a dot , that means we are sending the specific app to use
        if (Str::contains($role, '.')) {
            $appRole = explode('.', $role);
            $role = $appRole[1];
            $appName = $appRole[0];

            //look for the app and set it
            if ($app = Apps::getACLApp($appName)) {
                $this->setApp($app);
            }
        }

        return $role;
    }

    /**
     * {@inheritdoc}
     * Example:
     * <code>
     * //Add a resource to the the list allowing access to an action
     * $acl->addResource(new Phalcon\Acl\Component('customers'), 'search');
     * $acl->addResource('customers', 'search');
     * //Add a resource  with an access list
     * $acl->addResource(new Phalcon\Acl\Component('customers'), ['create', 'search']);
     * $acl->addResource('customers', ['create', 'search']);
     * $acl->addResource('App.customers', ['create', 'search']);
     * </code>.
     *
     * @param  \Phalcon\Acl\Component|string $resource
     * @param  array|string                 $accessList
     *
     * @return bool
     */
    public function addComponent($resource, $accessList = null) : bool
    {
        if (!is_object($resource)) {
            //check if we have a dot , that means we are sending the specific app to use
            $resource = $this->setAppByResource($resource);

            $resource = new Component($resource);
        }

        if (!ResourcesDB::isResource($resource->getName(), $this->getApp())) {
            $resourceDB = new ResourcesDB();
            $resourceDB->name = $resource->getName();
            $resourceDB->description = $resource->getDescription();
            $resourceDB->apps_id = $this->getApp()->getId();
            $resourceDB->saveOrFail();
        }

        if ($accessList) {
            return $this->addComponentAccess($resource->getName(), $accessList);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string       $resourceName
     * @param  array|string $accessList
     *
     * @return bool
     *
     * @throws \Phalcon\Acl\Exception
     */
    public function addComponentAccess($resourceName, $accessList) : bool
    {
        if (!ResourcesDB::isResource($resourceName, $this->getApp())) {
            throw new Exception("Resource '{$resourceName}' does not exist in ACL");
        }

        $resource = ResourcesDB::getByName($resourceName, $this->getApp());

        if (!is_array($accessList)) {
            $accessList = [$accessList];
        }

        foreach ($accessList as $accessName) {
            if (!ResourcesAccesses::exist($resource, $accessName)) {
                $resourceAccesses = new ResourcesAccesses();
                $resourceAccesses->beforeCreate(); //wtf?
                $resourceAccesses->resources_name = $resourceName;
                $resourceAccesses->access_name = $accessName;
                $resourceAccesses->apps_id = $this->getApp()->getId();
                $resourceAccesses->resources_id = $resource->getId();
                $resourceAccesses->saveOrFail();
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getComponents() : array
    {
        $resources = [];

        foreach (ResourcesDB::find() as $row) {
            $resources[] = new Component($row->name, $row->description);
        }
        return $resources;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRoles() : array
    {
        $roles = [];

        foreach (RolesDB::find() as $row) {
            $roles[] = new Role($row->name, $row->description);
        }
        return $roles;
    }

    /**
     * {@inheritdoc}
     *
     * @param string       $resourceName
     * @param array|string $accessList
     */
    public function dropComponentAccess($resourceName, $accessList) : void
    {
        throw new BadMethodCallException('Not implemented yet.');
    }

    /**
     * {@inheritdoc}
     * You can use '*' as wildcard
     * Example:
     * <code>
     * //Allow access to guests to search on customers
     * $acl->allow('guests', 'customers', 'search');
     * //Allow access to guests to search or create on customers
     * $acl->allow('guests', 'customers', ['search', 'create']);
     * //Allow access to any role to browse on products
     * $acl->allow('*', 'products', 'browse');
     * //Allow access to any role to browse on any resource
     * $acl->allow('*', '*', 'browse');
     * </code>.
     *
     * @param string       $roleName
     * @param string       $resourceName
     * @param array|string $access
     * @param mixed $func
     */
    public function allow($roleName, $resourceName, $access, $func = null) : void
    {
        $this->allowOrDeny($roleName, $resourceName, $access, AclEnum::ALLOW);
    }

    /**
     * {@inheritdoc}
     * You can use '*' as wildcard
     * Example:
     * <code>
     * //Deny access to guests to search on customers
     * $acl->deny('guests', 'customers', 'search');
     * //Deny access to guests to search or create on customers
     * $acl->deny('guests', 'customers', ['search', 'create']);
     * //Deny access to any role to browse on products
     * $acl->deny('*', 'products', 'browse');
     * //Deny access to any role to browse on any resource
     * $acl->deny('*', '*', 'browse');
     * </code>.
     *
     * @param  string       $roleName
     * @param  string       $resourceName
     * @param  array|string $access
     * @param  mixed $func
     *
     * @return void
     */
    public function deny($roleName, $resourceName, $access, $func = null) : void
    {
        $this->allowOrDeny($roleName, $resourceName, $access, AclEnum::DENY);
    }

    /**
     * {@inheritdoc}
     * Example:
     * <code>
     * //Does Andres have access to the customers resource to create?
     * $acl->isAllowed('Andres', 'Products', 'create');
     * //Do guests have access to any resource to edit?
     * $acl->isAllowed('guests', '*', 'edit');
     * </code>.
     *
     * @param string $role
     * @param string $resource
     * @param string $access
     * @param array  $parameters
     *
     * @return bool
     */
    public function isAllowed($role, $resource, $access, array $parameters = null) : bool
    {
        $role = $this->setAppByRole($role);
        //resource always overwrites the role app?
        $resource = $this->setAppByResource($resource);
        $roleObj = RolesDB::getByName($role);

        $sql = implode(' ', [
            'SELECT ' . $this->connection->escapeIdentifier('allowed') . ' FROM access_list AS a',
            // role_name in:
            'WHERE roles_id IN (',
            // given 'role'-parameter
            'SELECT roles_id ',
            // inherited role_names
            'UNION SELECT roles_inherit FROM roles_inherits WHERE roles_id = ?',
            // or 'any'
            "UNION SELECT '*'",
            ')',
            // resources_name should be given one or 'any'
            "AND resources_name IN (?, '*')",
            // access_name should be given one or 'any'
            //"AND access_name IN (?, '*')", you need to specify * , we are forcing to check always for permissions
            "AND access_name IN (?, '*')",
            'AND apps_id = ? ',
            'AND roles_id = ? ',
            'AND is_deleted = 0 ',
            // order be the sum of bool for 'literals' before 'any'
            'ORDER BY ' . $this->connection->escapeIdentifier('allowed') . ' DESC',
            // get only one...
            'LIMIT 1'
        ]);

        // fetch one entry...
        $allowed = $this->connection->fetchOne(
            $sql,
            Enum::FETCH_NUM,
            [
                $roleObj->getId(),
                $resource,
                $access,
                $this->getApp()->getId(),
                $roleObj->getId()
            ]
        );

        if (is_array($allowed)) {
            return (bool) $allowed[0];
        }

        /**
         * Return the default access action.
         */
        return (bool) $this->noArgumentsDefaultAction;
    }

    /**
     * Returns the default ACL access level for no arguments provided
     * in isAllowed action if there exists func for accessKey.
     *
     * @return int
     */
    public function getNoArgumentsDefaultAction() : int
    {
        return $this->noArgumentsDefaultAction;
    }

    /**
     * Sets the default access level for no arguments provided
     * in isAllowed action if there exists func for accessKey.
     *
     * @param int $defaultAccess Phalcon\AclEnum::ALLOW or Phalcon\AclEnum::DENY
     */
    public function setNoArgumentsDefaultAction($defaultAccess) : void
    {
        $this->noArgumentsDefaultAction = intval($defaultAccess);
    }

    /**
     * Inserts/Updates a permission in the access list.
     *
     * @param  string  $roleName
     * @param  string  $resourceName
     * @param  string  $accessName
     * @param  int $action
     *
     * @return bool
     *
     * @throws \Phalcon\Acl\Exception
     */
    protected function insertOrUpdateAccess($roleName, $resourceName, $accessName, $action)
    {
        $resourceName = $this->setAppByResource($resourceName);

        /**
         * Check if the access is valid in the resource unless wildcard.
         */
        if ($resourceName !== '*' && $accessName !== '*') {
            $resource = ResourcesDB::getByName($resourceName, $this->getApp());

            if (!ResourcesAccesses::exist($resource, $accessName)) {
                throw new Exception(
                    "Access '{$accessName}' does not exist in resource '{$resourceName}' ({$resource->getId()}) in ACL"
                );
            }
        }

        /**
         * Update the access in access_list.
         */

        $role = RolesDB::getByName($roleName);

        if (!AccessListDB::exist($role, $resourceName, $accessName)) {
            $accessListDB = new AccessListDB();
            $accessListDB->roles_id = $role->getId();
            $accessListDB->roles_name = $roleName;
            $accessListDB->resources_name = $resourceName;
            $accessListDB->access_name = $accessName;
            $accessListDB->allowed = $action;
            $accessListDB->apps_id = $this->getApp()->getId();
            $accessListDB->saveOrFail();
        } else {
            $accessListDB = accessListDB::getBy($role, $resourceName, $accessName);
            $accessListDB->allowed = $action;
            $accessListDB->updateOrFail();
        }

        /**
         * Update the access '*' in access_list.
         */
        if (!AccessListDB::exist($role, $resourceName, '*')) {
            $accessListDB = new AccessListDB();
            $accessListDB->roles_id = $role->getId();
            $accessListDB->roles_name = $roleName;
            $accessListDB->resources_name = $resourceName;
            $accessListDB->access_name = '*';
            $accessListDB->allowed = $this->noArgumentsDefaultAction;
            $accessListDB->apps_id = $this->getApp()->getId();
            $accessListDB->saveOrFail();
        }

        return true;
    }

    /**
     * Inserts/Updates a permission in the access list.
     *
     * @param  string       $roleName
     * @param  string       $resourceName
     * @param  array|string $access
     * @param  int      $action
     *
     * @throws \Phalcon\Acl\Exception
     */
    protected function allowOrDeny($roleName, $resourceName, $access, $action) : void
    {
        if (!RolesDB::isRole($roleName)) {
            throw new Exception("Role '{$roleName}' does not exist in the list");
        }
        if (!is_array($access)) {
            $access = [$access];
        }
        foreach ($access as $accessName) {
            $this->insertOrUpdateAccess($roleName, $resourceName, $accessName, $action);
        }
    }
}
