<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Database\HashTableTrait;
use Baka\Contracts\EventsManager\EventManagerAwareTrait;
use Baka\Contracts\Notifications\NotifiableTrait;
use Baka\Database\Exception\ModelNotProcessedException;
use function Baka\getShortClassName;
use Baka\Hashing\Keys;
use Baka\Hashing\Password;
use Baka\Validations\PasswordValidation;
use Canvas\Auth\App as AppAuth;
use Canvas\Contracts\Auth\TokenTrait;
use Canvas\Contracts\FileSystemModelTrait;
use Canvas\Contracts\PermissionsTrait;
use Canvas\Contracts\SubscriptionPlanLimitTrait;
use Canvas\Models\Behaviors\Uuid;
use Canvas\Models\Locations\Cities;
use Canvas\Models\Locations\Countries;
use Canvas\Models\Locations\States;
use Canvas\Utils\StringFormatter;
use Exception;
use Phalcon\Di;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use  Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

class Users extends AbstractModel implements UserInterface
{
    use PermissionsTrait;
    use SubscriptionPlanLimitTrait;
    use FileSystemModelTrait;
    use HashTableTrait;
    use NotifiableTrait;
    use EventManagerAwareTrait;
    use TokenTrait;

    /**
     * Constant for anonymous user.
     */
    const ANONYMOUS = '-1';

    public ?string $uuid = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $displayname = null;
    public ?string $registered = null;
    public ?string $lastvisit = null;
    public int $default_company = 0;
    public ?string $defaultCompanyName = null;
    public ?string $dob = null;
    public ?string $sex = null;
    public ?string $description = null;
    public ?string $phone_number = null;
    public ?string $cell_phone_number = null;
    public ?string $timezone = null;
    public ?int $city_id = 0;
    public ?int $state_id = 0;
    public ?int $country_id = 0;
    public ?string $user_recover_code = null;
    public int $welcome = 0;
    public int $user_active = 0;
    public ?string $user_activation_key = null;
    public ?string $user_activation_email = null;
    public ?string $profile_header = '';
    public bool $loggedIn = false;
    public ?string $location = null;
    public string $interest = '';
    public int $profile_privacy = 0;
    public ?string $user_activation_forgot = null;
    public ?string $language = null;
    public string $session_id = '';
    public string $session_key = '';
    public ?string $banned = null;
    public ?int $user_last_login_try = 0;
    public int $user_level = 0;
    public ?int $user_login_tries = 0;
    public ?int $session_time = null;
    public ?int $session_page = 0;
    public static string $locale = 'ja_jp';

    /**
     * @deprecated with filesystem
     */
    public ?string $profile_image = null;
    public ?string $profile_image_mobile = null;
    public ?string $profile_remote_image = null;
    public ?string $profile_image_thumb = ' ';
    public int $default_company_branch = 0;
    public ?int $roles_id = null;
    public ?string $stripe_id = null;
    public ?string $card_last_four = null;
    public ?string $card_brand = null;
    public ?string $trial_ends_at = null;

    /**
     * Provide the app plan id
     * if the user is signing up a new company.
     *
     * @var int
     */
    public ?int $appPlanId = null;

    /**
     * Active subscription id.Not an actual table field, used temporarily.
     *
     * @var string
     */
    public ?int $active_subscription_id = null;

    /**
     * System Module Id.
     *
     * @var int
     */
    public int $system_modules_id = 2;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users');
        $this->addBehavior(new Uuid());

        //overwrite parent relationships
        $this->hasOne('id', Sessions::class, 'users_id', ['alias' => 'session']);
        $this->hasMany('id', Sessions::class, 'users_id', ['alias' => 'sessions']);
        $this->hasMany('id', SessionKeys::class, 'users_id', ['alias' => 'sessionKeys']);
        $this->hasMany('id', Banlist::class, 'users_id', ['alias' => 'bans']);
        $this->hasMany('id', Sessions::class, 'users_id', ['alias' => 'sessions']);
        $this->hasMany('id', UserConfig::class, 'users_id', ['alias' => 'config']);
        $this->hasMany('id', UserLinkedSources::class, 'users_id', ['alias' => 'sources']);

        $this->hasOne(
            'default_company',
            'Canvas\Models\Companies',
            'id',
            [
                'alias' => 'defaultCompany',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'default_company',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'currentCompany', 'reusable' => true]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'users_id',
            [
                'alias' => 'companies',
                'reusable' => true,
                'params' => [
                    'conditions' => 'apps_id = ?0',
                    'bind' => [$this->getDI()->get('app')->getId()],
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'users_id',
            [
                'alias' => 'apps',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'users_id',
            [
                'alias' => 'app',
                'reusable' => true,
                'params' => [
                    'conditions' => 'apps_id = ?0',
                    'bind' => [Di::getDefault()->getApp()->getId()]
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'users_id',
            ['alias' => 'userWebhook']
        );

        $systemModule = SystemModules::getByModelName(self::class);
        $this->hasOne(
            'id',
            'Canvas\Models\FileSystemEntities',
            'entity_id',
            [
                'alias' => 'files',
                'params' => [
                    'conditions' => 'system_modules_id = ?0',
                    'bind' => [$systemModule->getId()],
                    'reusable' => true
                ]
            ]
        );
        $this->belongsTo('city_id', Cities::class, 'id', ['alias' => 'cities']);
        $this->belongsTo('state_id', States::class, 'id', ['alias' => 'states']);
        $this->belongsTo('country_id', Countries::class, 'id', ['alias' => 'countries']);
    }

    /**
     * Initialize relationship after fetch
     * since we need company id info.
     *
     * @return void
     */
    public function afterFetch()
    {
        $this->hasManyToMany(
            'id',
            'Canvas\Models\UserRoles',
            'users_id',
            'roles_id',
            'Canvas\Models\Roles',
            'id',
            [
                'alias' => 'roles',
                'reusable' => true,
                'params' => [
                    'limit' => 1,
                    'conditions' => 'Canvas\Models\UserRoles.apps_id = ' . $this->getDI()->get('app')->getId() . ' AND Canvas\Models\UserRoles.companies_id = ' . $this->currentCompanyId(),
                    'order' => 'Canvas\Models\UserRoles.apps_id desc',
                ]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\UserRoles',
            'users_id',
            [
                'alias' => 'userRole',
                'reusable' => true,
                'params' => [
                    'limit' => 1,
                    'conditions' => 'Canvas\Models\UserRoles.apps_id in (?0, ?1) AND Canvas\Models\UserRoles.companies_id = ' . $this->currentCompanyId(),
                    'bind' => [$this->getDI()->get('app')->getId(), Roles::DEFAULT_ACL_APP_ID],
                    'order' => 'apps_id desc',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserRoles',
            'users_id',
            [
                'alias' => 'permissions',
                'reusable' => true,
                'params' => [
                    'conditions' => 'Canvas\Models\UserRoles.apps_id = ' . $this->getDI()->get('app')->getId() . ' AND Canvas\Models\UserRoles.companies_id = ' . $this->currentCompanyId(),
                ]
            ]
        );
    }

    /**
     * Validations and business logic.
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            'email',
            new Email([
                'field' => 'email',
                'required' => true,
            ])
        );

        $validator->add(
            'displayname',
            new PresenceOf([
                'field' => 'displayname',
                'required' => true,
            ])
        );

        // Unique values
        $validator->add(
            'email',
            new Uniqueness([
                'field' => 'email',
                'message' => _('This email already has an account.'),
            ])
        );

        return $this->validate($validator);
    }

    /**
     * get the user by its Id, we can specify the cache if we want to
     * we only get result if the user is active.
     *
     * @param int $userId
     * @param bool $cache
     *
     * @return UserInterface
     */
    public static function getById($id, $cache = false) : UserInterface
    {
        $options = null;
        $key = 'userInfo_' . $id;

        if ($cache) {
            $options = ['cache' => ['lifetime' => 3600, 'key' => $key]];
        }

        return self::findFirstOrFail([
            'conditions' => 'id = ?0 and is_deleted = 0',
            'bind' => [$id]
        ]);
    }

    /**
     * get the user by its uuid, we can specify the cache if we want to
     * we only get result if the user is active.
     *
     * @param string $userUuid
     * @param bool $cache
     *
     * @return UserInterface
     */
    public static function getByUuid(string $userUuid, bool $cache = false) : UserInterface
    {
        $options = null;
        $key = 'userInfo_' . $userUuid;

        if ($cache) {
            $options = ['cache' => ['lifetime' => 3600, 'key' => $key]];
        }

        return self::findFirstOrFail([
            'conditions' => 'uuid = ?0 and is_deleted = 0',
            'bind' => [$userUuid]
        ]);
    }

    /**
     * is the user active?
     *
     * @return bool
     */
    public function isActive() : bool
    {
        return (bool) $this->user_active;
    }

    /**
     * get user by there email address.
     *
     * @return User|null
     */
    public static function getByEmail(string $email) : ?UserInterface
    {
        $user = self::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0',
            'bind' => [$email]
        ]);

        if (!$user) {
            throw new Exception('No User Found');
        }

        return $user;
    }

    /**
     * get user nickname.
     *
     * @return string
     */
    public function getDisplayName() : string
    {
        return strtolower($this->displayname);
    }

    /**
     * get user email.
     *
     * @return string
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * is the user logged in?
     *
     * @return bool
     */
    public function isLoggedIn() : bool
    {
        return $this->loggedIn;
    }

    /**
     * Is Anonymous user.
     *
     * @return bool
     */
    public function isAnonymous() : bool
    {
        return (int) $this->getId() == self::ANONYMOUS;
    }

    /**
     * get the user sex, not get sex from the user :P.
     *
     * @return string
     */
    public function getSex() : string
    {
        if ($this->sex == 'M') {
            return _('Male');
        } elseif ($this->sex == 'F') {
            return _('Female');
        } else {
            return _('Undefined');
        }
    }

    /**
     * Log a user out of the system.
     *
     * @deprecated v0.4
     *
     * @return bool
     */
    public function logOut(?string $ip = null) : bool
    {
        $session = new Sessions();
        $session->end($this, $ip);

        return true;
    }

    /**
     * Clean the user session from the system.
     *
     * @deprecated v0.4
     *
     * @return true
     */
    public function cleanSession() : bool
    {
        $session = new Sessions();
        $session->end($this);

        return true;
    }

    /**
     * get the user session id.
     *
     * @return string
     */
    public function getSessionId() : string
    {
        //if its empty get it from the relationship, else get it from the property
        return empty($this->session_id) ? $this->getSession(['order' => 'time desc'])->session_id : $this->session_id;
    }

    /**
     * get the user language.
     *
     * @return string
     */
    public function getLanguage() : ? string
    {
        return $this->language;
    }

    /**
     * Determine if a user is banned.
     *
     * @return bool
     */
    public function isBanned() : bool
    {
        return !$this->isActive() && $this->banned === 'Y';
    }

    /**
     * Set hashtable settings table, userConfig ;).
     *
     * @return void
     */
    protected function createSettingsModel() : void
    {
        $this->settingsModel = new UserConfig();
    }

    /**
     * Get the User key for redis.
     *
     * @return string
     */
    public function getKey() : int
    {
        return $this->id;
    }

    /**
     * A company owner is the first person that register this company
     * This only ocurred when signing up the first time, after that all users invites
     * come with a default_company id attached.
     *
     * @return bool
     */
    public function isFirstSignup() : bool
    {
        return empty($this->default_company);
    }

    /**
     * Before create.
     *
     * @return void
     */
    public function beforeCreate()
    {
        parent::beforeCreate();

        $this->phone_number = StringFormatter::sanitizePhoneNumber($this->phone_number);
        $this->cell_phone_number = StringFormatter::sanitizePhoneNumber($this->cell_phone_number);

        $random = new Random();
        $this->user_activation_email = $random->uuid();
        //this is only empty when creating a new user
        if (!$this->isFirstSignup()) {
            //confirm if the app reach its limit
            $this->isAtLimit();
        }

        $role = Roles::getByName('Admins');
        $this->roles_id = $this->roles_id ?? $role->getId();
    }

    /**
     * Before saving the user.
     *
     * @return void
     */
    public function beforeSave()
    {
        $this->phone_number = StringFormatter::sanitizePhoneNumber($this->phone_number);
        $this->cell_phone_number = StringFormatter::sanitizePhoneNumber($this->cell_phone_number);
    }

    /**
     * What the current company the users is logged in with
     * in this current session?
     *
     * @return int
     */
    public function currentCompanyId() : int
    {
        return  (int) $this->get(Companies::cacheKey());
    }

    /**
     * Whats the current default branch of the current company.
     *
     * @return int
     */
    public function currentBranchId() : int
    {
        $branchId = $this->get($this->getDefaultCompany()->branchCacheKey());
        if (is_null($branchId)) {
            $branchId = $this->getDefaultCompany()->defaultBranch->getId();

            /**
             * @todo Remove this later in future versions.
             */
            $this->set($this->getDefaultCompany()->branchCacheKey(), $branchId);
        }

        return $branchId;
    }

    /**
     * Overwrite the user relationship.
     * use Phalcon Registry to assure we maintain the same instance across the request.
     */
    public function getDefaultCompany() : Companies
    {
        $registry = Di::getDefault()->get('registry');
        $key = 'company_' . Di::getDefault()->get('app')->getId() . '_' . $this->getId();
        if (!isset($registry[$key])) {
            $registry[$key] = Companies::findFirstOrFail($this->currentCompanyId());
        }
        return  $registry[$key];
    }

    /**
     * Get the default company Group.
     *
     * @return CompaniesGroups
     */
    public function getDefaultCompanyGroup() : CompaniesGroups
    {
        return $this->getDefaultCompany()->getDefaultCompanyGroup();
    }

    /**
     * What the current company brach the users is logged in with
     * in this current session?
     *
     * @return int
     */
    public function currentCompanyBranchId() : int
    {
        return (int) $this->default_company_branch;
    }

    /**
     * What to do after the creation of a new users
     *  - Assign default role.
     *
     * @return void
     */
    public function afterCreate()
    {
        //need to run it here, since we overwrite the default_company id and null this function objective
        $isFirstSignup = $this->isFirstSignup();

        /**
         * if we don't find the userdata di lets create it.
         *
         * @todo this is not ideal lets fix it later
         */
        if (!$this->getDI()->has('userData')) {
            $this->getDI()->setShared('userData', $this);
        }

        $this->fire('user:afterSignup', $this, $isFirstSignup);

        //update user activity when its not a empty user
        if (!$isFirstSignup) {
            $this->updateAppActivityLimit();
        }
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
        $this->associateFileSystem();
    }

    /**
     * update user role for the specific app.
     *
     * @return bool
     */
    protected function updatePermissionRoles() : bool
    {
        if ($permission = $this->getPermission()) {
            $permission->roles_id = $this->roles_id;
            return $permission->updateOrFail();
        }

        return false;
    }

    /**
     * Overwrite the permission relationship to force the user of company id.
     *
     * @return UserRoles
     */
    public function getPermission()
    {
        return $this->getUserRole();
    }

    /**
     * Get the list of all the associated apps this users has.
     *:w.
     *
     * @return array
     */
    public function getAssociatedApps() : array
    {
        $apps = $this->getApps(['columns' => 'apps_id', 'group' => 'apps_id']);

        if ($apps->count()) {
            return array_map(function ($apps) {
                return $apps['apps_id'];
            }, $apps->toArray());
        }

        return [0];
    }

    /**
     * Get an array of the associates companies Ids.
     *
     * @return array
     */
    public function getAssociatedCompanies() : array
    {
        $companies = $this->getCompanies(['columns' => 'companies_id']);

        if ($companies->count()) {
            return array_map(function ($company) {
                return $company['companies_id'];
            }, $companies->toArray());
        }

        return [0];
    }

    /**
     * Get user by key.
     *
     * @param string $userActivationEmail
     *
     * @return Users
     */
    public static function getByUserActivationEmail(string $userActivationEmail) : Users
    {
        return self::findFirstOrFail([
            'conditions' => 'user_activation_email = ?0 and user_active =?1 and is_deleted = 0',
            'bind' => [$userActivationEmail, 1],
        ]);
    }

    /**
     * Overwrite the relationship.
     *
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->getFileByName('photo');
    }

    /**
     * Update the user current default company.
     *
     * @param int $companyId
     *
     * @return void
     */
    public function switchDefaultCompanyByBranch(int $branchId) : void
    {
        if ($branch = CompaniesBranches::findFirst($branchId)) {
            if ($branch->company) {
                if ($branch->company->userAssociatedToCompany($this)) {
                    $this->default_company = $branch->company->getId();
                    $this->default_company_branch = $branch->getId();
                    //set the default company id per the specific app , we do this so we can have multiple default companies per diff apps
                    $this->set(Companies::cacheKey(), $this->default_company);
                    $this->set($branch->company->branchCacheKey(), $branch->getId());
                }
            }
        }
    }

    /**
     * Update the password for a current user.
     *
     * @param string $newPassword
     *
     * @return bool
     */
    public function updatePassword(string $currentPassword, string $newPassword, string $verifyPassword) : bool
    {
        $currentPassword = trim($currentPassword);
        $newPassword = trim($newPassword);
        $verifyPassword = trim($verifyPassword);

        $app = $this->getDI()->get('app');

        if (!$app->ecosystemAuth()) {
            $userAppData = $this->getApp([
                'conditions' => 'companies_id = :id:',
                'bind' => [
                    'id' => $this->currentCompanyId()
                ]
            ]);

            $password = $userAppData->password;
        } else {
            $password = $this->password;
        }

        // First off check that the current password matches the current password
        if (Password::check($currentPassword, $password)) {
            PasswordValidation::validate($newPassword, $verifyPassword);

            return $this->resetPassword($newPassword);
        }

        throw new Exception(_(' Your current password is incorrect .'));
    }

    /**
     * Reset the user password.
     *
     * @param string $newPassword
     *
     * @return bool
     */
    public function resetPassword(string $newPassword) : bool
    {
        $app = $this->getDI()->get('app');

        if (!$app->ecosystemAuth()) {
            //update all companies password for the current user app
            AppAuth::updatePassword($this, Password::make($newPassword));
        } else {
            $this->password = Password::make($newPassword);
        }

        return true;
    }

    /**
     * Generate new forgot password hash.
     *
     * @return string
     */
    public function generateForgotHash() : string
    {
        $this->user_activation_forgot = Keys::make();
        $this->updateOrFail();

        return $this->user_activation_forgot;
    }

    /**
     * Generate new forgot password code.
     *
     * @return string
     */
    public function generateForgotCode() : string
    {
        $random = new Random();
        $this->user_recover_code = sprintf('%06d', $random->number(999999));
        $this->updateOrFail();

        return $this->user_recover_code;
    }

    /**
     * Generate a default displayname by firstname and lastname.
     * If firstname is set to the name of app then generate a random displayname.
     *
     * @return string
     */
    public function generateDefaultDisplayname() : string
    {
        if (empty($this->firstname) && empty($this->lastname)) {
            $appName = $this->getDI()->get('app')->name;
            $random = new Random();
            $this->lastname = 'User';
            $this->firstname = $appName;

            return  $appName . $random->number(99999999);
        }

        return $this->firstname . '.' . $this->lastname;
    }

    /**
     * Verify if the user bellow to the current app.
     *
     * @return bool
     */
    public function inApp() : bool
    {
        $appId = $this->getDI()->get('app')->getId();

        if ($this->getApps("apps_id = {$appId}")->count()) {
            return true;
        }

        throw new Exception(getShortClassName($this) . ' Record not found');
    }

    /**
     * Throws an exception with including all validation messages that were retrieved.
     *
     * @todo lets add a configuration to remove the Model name of the exception in Kanvas
     *
     * @throws ModelNotProcessedException
     */
    protected function throwErrorMessages() : void
    {
        throw new ModelNotProcessedException(
            current($this->getMessages())->getMessage()
        );
    }

    /**
     * Verify that the user is unsubscribed from email.
     *
     * @param int $notificationTypeId
     *
     * @return bool
     */
    public function isUnsubscribe(int $notificationTypeId) : bool
    {
        return NotificationsUnsubscribe::isUnsubscribe($this, $notificationTypeId);
    }

    /**
     * unsubscribe user for NotificationType.
     *
     * @param int $notificationTypeId
     *
     * @return NotificationsUnsubscribe
     */
    public function unsubscribe(int $notificationTypeId) : NotificationsUnsubscribe
    {
        $notificationType = NotificationType::findFirst($notificationTypeId);
        $systemModulesId = $notificationType ? $notificationType->system_modules_id : -1;
        return Notifications::unsubscribe($this, (int) $notificationTypeId, (int) $systemModulesId);
    }
}
