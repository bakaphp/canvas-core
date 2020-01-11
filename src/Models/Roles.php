<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Acl\Role as AclRole;
use Canvas\Exception\ModelException;
use Canvas\Http\Exception\InternalServerErrorException;
use Canvas\Http\Exception\UnprocessableEntityException;

/**
 * Class Roles.
 *
 * @package Canvas\Models
 *
 * @property AccesList $accesList
 * @property \Phalcon\Di $di
 */
class Roles extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var integer
     */
    public $scope;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var int
     */
    public $apps_id;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

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
                'messageMinimum' => _('Role Name. Maxium 32 characters.'),
            ])
        );

        return $this->validate($validator);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'roles';
    }

    /**
     * Check if the role existe in the db.
     *
     * @param AclRole $role
     * @return int
     */
    public static function exist(AclRole $role): int
    {
        return self::count([
            'conditions' => 'name = ?0 AND companies_id = ?1 AND apps_id = ?2',
            'bind' => [
                $role->getName(),
                Di::getDefault()->getUserData()->currentCompanyId(),
                Di::getDefault()->getAcl()->getApp()->getId()
            ]
        ]);
    }

    /**
     * check if this string is already a role
     * whats the diff with exist or why not merge them? exist uses the alc object and only check
     * with your current app, this also check with de defautl company ap.
     *
     * @param string $roleName
     * @return boolean
     */
    public static function isRole(string $roleName) : bool
    {
        return (bool) self::count([
            'conditions' => 'name = ?0 AND apps_id in (?1, ?3) AND companies_id in (?2, ?3)',
            'bind' => [
                $roleName,
                Di::getDefault()->getAcl()->getApp()->getId(),
                Di::getDefault()->getUserData()->currentCompanyId(),
                Apps::CANVAS_DEFAULT_APP_ID
            ]
        ]);
    }

    /**
     * Get the entity by its name.
     *
     * @param string $name
     * @return Roles
     */
    public static function getByName(string $name): Roles
    {
        $role = self::findFirst([
            'conditions' => 'name = ?0 AND apps_id in (?1, ?3) AND companies_id in (?2, ?3) AND is_deleted = 0',
            'bind' => [
                $name,
                Di::getDefault()->getAcl()->getApp()->getId(),
                Di::getDefault()->getUserData()->currentCompanyId(),
                Apps::CANVAS_DEFAULT_APP_ID
            ],
            'order' => 'apps_id DESC'
        ]);

        if (!is_object($role)) {
            throw new UnprocessableEntityException(_('Roles ' . $name . ' not found on this app ' . Di::getDefault()->getAcl()->getApp()->getId() . ' AND Company ' . Di::getDefault()->getUserData()->currentCompanyId()));
        }

        return $role;
    }

    /**
     * Get the entity by its name.
     *
     * @param string $name
     * @return Roles
     */
    public static function getById(int $id): Roles
    {
        return self::findFirstOrFail([
            'conditions' => 'id = ?0 AND companies_id in (?1, ?2) AND apps_id in (?3, ?4) AND is_deleted = 0',
            'bind' => [
                $id,
                Di::getDefault()->getUserData()->currentCompanyId(),
                Apps::CANVAS_DEFAULT_APP_ID,
                Di::getDefault()->getApp()->getId(),
                Apps::CANVAS_DEFAULT_APP_ID
            ],
            'order' => 'apps_id DESC'
        ]);
    }

    /**
     * Get the Role by it app name.
     *
     * @param string $role
     * @return Roles
     */
    public static function getByAppName(string $role, Companies $company): Roles
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

        return self::findFirst([
            'conditions' => 'apps_id in (?0, ?1) AND companies_id in (?2 , ?3)',
            'bind' => [
                $app->getId(),
                self::DEFAULT_ACL_APP_ID,
                $company->getId(),
                self::DEFAULT_ACL_COMPANY_ID
            ]
        ]);
    }

    /**
     * Duplicate a role with it access list.
     *
     * @return Roles
     */
    public function copy(): Roles
    {
        $accesList = $this->accesList;

        //remove id to create new record
        $this->name .= 'Copie';
        $this->scope = 1;
        $this->id = null;
        $this->companies_id = $this->di->getUserData()->currentCompanyId();
        $this->apps_id = $this->di->getApp()->getId();
        $this->save();

        foreach ($accesList as $access) {
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
     * @return boolean
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
            foreach ($this->accesList as $access) {
                $access->softDelete();
            }
        }
    }

    /**
     * Check if role exists by its id.
     * @param integer $role_id
     * @return Roles
     */
    public static function existsById(int $id): Roles
    {
        $role = self::getById($id);

        if (!is_object($role)) {
            throw new ModelException('Role does not exist');
        }

        return $role;
    }

    /**
     * Assign the default app role to a given user.
     *
     * @param Users $user
     * @return bool
     */
    public static function assignDefault(Users $user): bool
    {
        $apps = Di::getDefault()->getApp();
        $userRoles = UserRoles::findFirst([
            'conditions' => 'users_id = ?0 AND apps_id = ?1 AND companies_id = ?2 AND is_deleted = 0',
            'bind' => [$user->getId(), $apps->getId(), $user->getDefaultCompany()->getId()]
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
}
