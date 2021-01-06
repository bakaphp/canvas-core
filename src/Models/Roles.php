<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Exception\ModelNotFoundException;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\UnprocessableEntityException;
use Phalcon\Acl\Role as AclRole;
use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Uniqueness;

class Roles extends AbstractModel
{
    public string $name;
    public ?string $description;
    public ?int $scope;
    public int $companies_id;
    public int $apps_id;
    public int $is_default = 0;
    public int $is_active = 1;

    /**
     * Default ACL company.
     *
     */
    const DEFAULT_ACL_COMPANY_ID = 1;
    const DEFAULT_ACL_APP_ID = 1;
    const DEFAULT = 'Admins';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('roles');

        $this->hasMany(
            'id',
            'Canvas\Models\AccessList',
            'roles_id',
            ['alias' => 'accesList']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\AccessList',
            'roles_id',
            ['alias' => 'accessList']
        );
    }

    /**
     * Validations and business logic.
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'name',
            new PresenceOf([
                'field' => 'name',
                'required' => true,
            ])
        );

        $validator->add(
            'description',
            new PresenceOf([
                'field' => 'description',
                'required' => true,
            ])
        );

        $validator->add(
            'name',
            new StringLength([
                'max' => 32,
                'messageMinimum' => _('Role Name. Maximum 32 characters.'),
            ])
        );

        $validator->add(
            ['name', 'companies_id', 'apps_id'],
            new Uniqueness(
                [
                    'message' => 'Can\'t have 2 roles with the same name - ' . $this->name
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Check if the role existe in the db.
     *
     * @param AclRole $role
     *
     * @return int
     */
    public static function exist(AclRole $role) : int
    {
        $companyId = Di::getDefault()->get('acl')->getCompany()->getId();

        return self::count([
            'conditions' => 'name = ?0 AND companies_id = ?1 AND apps_id = ?2',
            'bind' => [
                $role->getName(),
                $companyId,
                Di::getDefault()->get('acl')->getApp()->getId()
            ]
        ]);
    }

    /**
     * check if this string is already a role
     * whats the diff with exist or why not merge them? exist uses the alc object and only check
     * with your current app, this also check with de defautl company ap.
     *
     * @param string $roleName
     *
     * @return bool
     */
    public static function isRole(string $roleName) : bool
    {
        $companyId = Di::getDefault()->get('acl')->getCompany()->getId();

        return (bool) self::count([
            'conditions' => 'name = ?0 AND apps_id in (?1, ?3) AND companies_id in (?2, ?3)',
            'bind' => [
                $roleName,
                Di::getDefault()->get('acl')->getApp()->getId(),
                $companyId,
                Apps::CANVAS_DEFAULT_APP_ID
            ]
        ]);
    }

    /**
     * Get the entity by its name.
     *
     * @param string $name
     *
     * @return Roles
     */
    public static function getByName(string $name) : Roles
    {
        $companyId = Di::getDefault()->get('acl')->getCompany()->getId();

        $role = self::findFirst([
            'conditions' => 'name = ?0 AND apps_id in (?1, ?3) AND companies_id in (?2, ?3) AND is_deleted = 0',
            'bind' => [
                $name,
                Di::getDefault()->get('acl')->getApp()->getId(),
                $companyId,
                Apps::CANVAS_DEFAULT_APP_ID
            ],
            'order' => 'apps_id DESC'
        ]);

        if (!is_object($role)) {
            throw new UnprocessableEntityException(
                _('Roles ' . $name . ' not found on this app ' . Di::getDefault()->get('acl')->getApp()->getId() . ' AND Company ' . Di::getDefault()->getUserData()->currentCompanyId())
            );
        }

        return $role;
    }

    /**
     * Get the entity by its name.
     *
     * @param string $name
     *
     * @return Roles
     */
    public static function getById(int $id) : Roles
    {
        $companyId = Di::getDefault()->get('acl')->getCompany()->getId();

        return self::findFirstOrFail([
            'conditions' => 'id = ?0 AND companies_id in (?1, ?2) AND apps_id in (?3, ?4) AND is_deleted = 0',
            'bind' => [
                $id,
                $companyId,
                self::DEFAULT_ACL_COMPANY_ID,
                Di::getDefault()->get('acl')->getApp()->getId(),
                Apps::CANVAS_DEFAULT_APP_ID
            ],
            'order' => 'apps_id DESC'
        ]);
    }

    /**
     * Get the Role by it app name.
     *
     * @param string $role
     *
     * @return Roles
     */
    public static function getByAppName(string $role, Companies $company) : Roles
    {
        //echeck if we have a dot , taht means we are sending the specific app to use
        if (strpos($role, '.') === false) {
            throw new InternalServerErrorException('ACL - We are expecting the app for this role');
        }

        $appRole = explode('.', $role);
        $role = $appRole[1];
        $appName = $appRole[0];

        //look for the app and set it
        if (!$app = Apps::getACLApp($appName)) {
            throw new InternalServerErrorException('ACL - No app found for this role');
        }

        return self::findFirstOrFail([
            'conditions' => 'name = ?0 and apps_id in (?1, ?2) AND companies_id in (?3 , ?4)',
            'bind' => [
                $role,
                $app->getId(),
                self::DEFAULT_ACL_APP_ID,
                $company->getId(),
                self::DEFAULT_ACL_COMPANY_ID
            ],
            'order' => 'apps_id DESC'
        ]);
    }

    /**
     * Duplicate a role with it access list.
     *
     * @return Roles
     */
    public function copy() : Roles
    {
        $accessList = $this->accessList;

        //remove id to create new record
        $this->name .= 'Copie';
        $this->scope = 1;
        $this->id = null;
        $this->companies_id = $this->di->getUserData()->currentCompanyId();
        $this->apps_id = $this->di->getApp()->getId();
        $this->save();

        foreach ($accessList as $access) {
            $copyAccessList = new AccessList();
            $copyAccessList->apps_id = $this->apps_id;
            $copyAccessList->roles_id = $this->getId();
            $copyAccessList->roles_name = $this->name;
            $copyAccessList->resources_name = $access->resources_name;
            $copyAccessList->access_name = $access->access_name;
            $copyAccessList->allowed = $access->allowed;
            $copyAccessList->create();
        }

        return $this;
    }

    /**
     * Add inherit to a given role.
     *
     * @param string $roleName
     * @param string $roleToInherit
     *
     * @return bool
     */
    public static function addInherit(string $roleName, string $roleToInherit)
    {
        $role = self::findFirstByName($roleName);

        if (!is_object($role)) {
            throw new UnprocessableEntityException("Role '{$roleName}' does not exist in the role list");
        }

        $inheritExist = RolesInherits::count([
            'conditions' => 'roles_name = ?0 and roles_inherit = ?1',
            'bind' => [$role->name, $roleToInherit]
        ]);

        if (!$inheritExist) {
            $rolesInHerits = new RolesInherits();
            $rolesInHerits->roles_name = $role->name;
            $rolesInHerits->roles_id = $role->getId();
            $rolesInHerits->roles_inherit = (int) $roleToInherit;

            if (!$rolesInHerits->save()) {
                throw new UnprocessableEntityException((string) current($rolesInHerits->getMessages()));
            }

            return true;
        }

        return false;
    }

    /**
     * After update.
     *
     * @return void
     */
    public function afterUpdate()
    {
        //if we deleted the role
        if ($this->is_deleted) {
            //delete
            foreach ($this->accessList as $access) {
                $access->softDelete();
            }
        }
    }

    /**
     * Check if role exists by its id.
     *
     * @param int $role_id
     *
     * @return Roles
     */
    public static function existsById(int $id) : Roles
    {
        $role = self::getById($id);

        if (!is_object($role)) {
            throw new ModelNotFoundException('Role does not exist');
        }

        return $role;
    }

    /**
     * Assign the default app role to a given user.
     *
     * @param Users $user
     *
     * @return bool
     */
    public static function assignDefault(Users $user) : bool
    {
        $apps = Di::getDefault()->getApp();
        $userRoles = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 AND apps_id = ?1 AND companies_id = ?2 AND is_deleted = 0',
            'bind' => [
                $user->getId(),
                $apps->getId(),
                $user->getDefaultCompany()->getId()
            ]
        ]);

        if (!is_object($userRoles)) {
            $userRole = new UserRoles();
            $userRole->users_id = $user->getId();
            $userRole->roles_id = Roles::getByName(Roles::DEFAULT)->getId();
            $userRole->apps_id = $apps->getId();
            $userRole->companies_id = $user->getDefaultCompany()->getId();
            return $userRole->saveOrFail();
        }

        return true;
    }

    /**
     * check if role is default or not.
     *
     * @return bool
     */
    public function isDefault() : bool
    {
        return (bool) $this->is_default;
    }
}
