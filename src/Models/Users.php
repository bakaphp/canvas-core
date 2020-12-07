<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Auth\Models\Users as BakUser;
use Baka\Cashier\Billable;
use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Database\HashTableTrait;
use Baka\Contracts\EventsManager\EventManagerAwareTrait;
use Baka\Contracts\Notifications\NotifiableTrait;
use Baka\Hashing\Keys;
use Baka\Hashing\Password;
use Baka\Validations\PasswordValidation;
use Canvas\Auth\App as AppAuth;
use Canvas\Models\Locations\Cities;
use Canvas\Models\Locations\Countries;
use Canvas\Models\Locations\States;
use Canvas\Traits\FileSystemModelTrait;
use Canvas\Traits\PermissionsTrait;
use Canvas\Traits\SubscriptionPlanLimitTrait;
use Carbon\Carbon;
use Exception;
use Phalcon\Di;
use Phalcon\Security\Random;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use ReflectionClass;

class Users extends BakUser implements UserInterface
{
    use PermissionsTrait;
    use Billable;
    use SubscriptionPlanLimitTrait;
    use FileSystemModelTrait;
    use HashTableTrait;
    use NotifiableTrait;
    use EventManagerAwareTrait;

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
    public ?string $active_subscription_id = null;

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

        //overwrite parent relationships
        $this->hasOne('id', 'Baka\Auth\Models\Sessions', 'users_id', ['alias' => 'session']);
        $this->hasMany('id', 'Baka\Auth\Models\Sessions', 'users_id', ['alias' => 'sessions']);
        $this->hasMany('id', 'Baka\Auth\Models\SessionKeys', 'users_id', ['alias' => 'sessionKeys']);
        $this->hasMany('id', 'Baka\Auth\Models\Banlist', 'users_id', ['alias' => 'bans']);
        $this->hasMany('id', 'Baka\Auth\Models\Sessions', 'users_id', ['alias' => 'sessions']);
        $this->hasMany('id', 'Canvas\Models\UserConfig', 'users_id', ['alias' => 'config']);
        $this->hasMany('id', 'Canvas\Models\UserLinkedSources', 'users_id', ['alias' => 'sources']);

        $this->hasOne(
            'default_company',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'defaultCompany']
        );

        $this->hasOne(
            'default_company',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'currentCompany']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\Subscription',
            'user_id',
            [
                'alias' => 'allSubscriptions',
                'params' => [
                    'conditions' => 'apps_id = ?0',
                    'bind' => [$this->di->getApp()->getId()],
                    'order' => 'id DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'users_id',
            [
                'alias' => 'companies',
                'params' => [
                    'conditions' => 'apps_id = ?0',
                    'bind' => [$this->di->getApp()->getId()],
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'users_id',
            [
                'alias' => 'apps',
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'users_id',
            [
                'alias' => 'app',
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

        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $this->hasOne(
            'id',
            'Canvas\Models\FileSystemEntities',
            'entity_id',
            [
                'alias' => 'files',
                'params' => [
                    'conditions' => 'system_modules_id = ?0',
                    'bind' => [$systemModule->getId()]
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
                'params' => [
                    'limit' => 1,
                    'conditions' => 'Canvas\Models\UserRoles.apps_id = ' . $this->di->getApp()->getId() . ' AND Canvas\Models\UserRoles.companies_id = ' . $this->currentCompanyId(),
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
                'params' => [
                    'limit' => 1,
                    'conditions' => 'Canvas\Models\UserRoles.apps_id in (?0, ?1) AND Canvas\Models\UserRoles.companies_id = ' . $this->currentCompanyId(),
                    'bind' => [$this->di->getApp()->getId(), Roles::DEFAULT_ACL_APP_ID],
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
                'params' => [
                    'conditions' => 'Canvas\Models\UserRoles.apps_id = ' . $this->di->getApp()->getId() . ' AND Canvas\Models\UserRoles.companies_id = ' . $this->currentCompanyId(),
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
     * Get all of the subscriptions for the user.
     */
    public function subscriptions()
    {
        $this->hasMany(
            'id',
            'Canvas\Models\Subscription',
            'user_id',
            [
                'alias' => 'subscriptions',
                'params' => [
                    'conditions' => 'apps_id = ?0 and companies_id = ?1',
                    'bind' => [$this->di->getApp()->getId(), $this->default_company],
                    'order' => 'id DESC'
                ]
            ]
        );

        return $this->getRelated('subscriptions');
    }

    /**
     * Strat a free trial.
     *
     * @param Users $user
     *
     * @return Subscription
     */
    public function startFreeTrial() : Subscription
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $trialEndsAt = Carbon::now()->addDays($this->di->getApp()->plan->free_trial_dates);

        $subscription = new Subscription();
        $subscription->user_id = $this->getId();
        $subscription->companies_id = $this->default_company;
        $subscription->apps_id = $this->di->getApp()->getId();
        $subscription->apps_plans_id = $this->di->getApp()->default_apps_plan_id;
        $subscription->name = $defaultPlan->name;
        $subscription->stripe_id = $defaultPlan->stripe_id;
        $subscription->stripe_plan = $defaultPlan->stripe_plan;
        $subscription->quantity = 1;
        $subscription->trial_ends_at = $trialEndsAt->toDateTimeString();
        $subscription->trial_ends_days = $trialEndsAt->diffInDays(Carbon::now());
        $subscription->is_freetrial = 1;
        $subscription->is_active = 1;
        $subscription->saveOrFail();

        $this->trial_ends_at = $subscription->trial_ends_at;
        $this->updateOrFail();

        return $subscription;
    }

    /**
     * Before create.
     *
     * @return void
     */
    public function beforeCreate()
    {
        parent::beforeCreate();
        $random = new Random();
        $this->user_activation_email = $random->uuid();

        //this is only empty when creating a new user
        if (!$this->isFirstSignup()) {
            //confirm if the app reach its limit
            $this->isAtLimit();
        }

        $role = Roles::getByName('Admins');
        $this->roles_id = $role->getId();
    }

    /**
     * What the current company the users is logged in with
     * in this current session?
     *
     * @return int
     */
    public function currentCompanyId() : int
    {
        $defaultCompanyId = $this->get(Companies::cacheKey());
        return !is_null($defaultCompanyId) ? (int) $defaultCompanyId : (int) $this->default_company;
    }

    /**
     * Overwrite the user relationship.
     * use Phalcon Registry to assure we mantian the same instance accross the request.
     */
    public function getDefaultCompany() : Companies
    {
        $registry = Di::getDefault()->getRegistry();
        $key = 'company_' . Di::getDefault()->getApp()->getId() . '_' . $this->getId();
        if (!isset($registry[$key])) {
            $registry[$key] = Companies::findFirstOrFail($this->currentCompanyId());
        }
        return  $registry[$key];
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
         * if we dont find the userdata di lets create it.
         *
         * @todo this is not ideal lets fix it later
         */
        if (!$this->di->has('userData')) {
            $this->di->setShared('userData', $this);
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
     * @return void
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
        return self::findFirst([
            'conditions' => 'user_activation_email = ?0 and user_active =?1 and is_deleted = 0',
            'bind' => [$userActivationEmail, 1],
        ]);
    }

    /**
     * Overwrite the relationship.
     *
     * @return void
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
                    //set the default company id per the specific app , we do this so we can have multip default companies per diff apps
                    $this->set(Companies::cacheKey(), $this->default_company);
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

        $app = Di::getDefault()->getApp();

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
        $app = Di::getDefault()->getApp();

        if (!$app->ecosystemAuth()) {
            //update all companies password for the current user app
            AppAuth::updatePassword($this, Password::make($newPassword));
        } else {
            $this->password = Password::make($newPassword);
        }

        return true;
    }

    /**
     * user signup to the service.
     *
     * did we find the email?
     * does it have access to this app?
     * no?
     * ok lets register / associate to this app
     * yes?
     * it meas he was invites so get the fuck out?
     *
     * @deprecated v1
     *
     * @return Users
     */
    public function signUp() : BakUser
    {
        $app = Di::getDefault()->getApp();

        if (!$app->ecosystemAuth()) {
            try {
                $user = self::getByEmail($this->email);

                $userAppData = $user->countApps('apps_id = ' . $this->getDI()->getDefault()->getApp()->getId());

                if ($userAppData > 0) {
                    throw new Exception('This email already has an account.');
                }

                //assign user role for the current app
                $user->roles_id = Roles::getByName(Roles::DEFAULT)->getId();

                $this->fire('user:afterSignup', $user, true);

                //update the passwords for the current app
                AppAuth::updatePassword($user, Password::make($this->password));

                //overwrite the current user object
                $this->id = $user->getId();
                $this->email = $user->getEmail();
            } catch (Exception $e) {
                //if we cant find the user normal signup
                $user = parent::signUp();

                //update all the password for the apps
                AppAuth::updatePassword($user, $this->password);
            }
        } else {
            $user = parent::signUp();
        }

        return $user;
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
     * Generate a default displayname by firstname and lastname.
     * If firstname is set to the name of app then generate a random displayname.
     *
     * @return string
     */
    public function generateDefaultDisplayname() : string
    {
        if (empty($this->firstname) && empty($this->lastname)) {
            $appName = Di::getDefault()->getApp()->name;
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
        $appId = Di::getDefault()->get('app')->getId();

        if ($this->getApps("apps_id = {$appId}")->count()) {
            return true;
        }

        throw new Exception((new ReflectionClass(new static))->getShortName() . ' Record not found');
    }
}
