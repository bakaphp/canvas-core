<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Traits\PermissionsTrait;
use Canvas\Traits\SubscriptionPlanLimitTrait;
use Phalcon\Cashier\Billable;
use Canvas\Exception\ServerErrorHttpException;
use Exception;
use Carbon\Carbon;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Uniqueness;
use Canvas\Traits\FileSystemModelTrait;
use Phalcon\Security\Random;
use Baka\Database\Contracts\HashTableTrait;
use Canvas\Contracts\Notifications\NotifiableTrait;
use Phalcon\Traits\EventManagerAwareTrait;
use Phalcon\Di;
use RuntimeException;

/**
 * Class Users.
 *
 * @package Canvas\Models
 *
 * @property Users $user
 * @property Config $config
 * @property Apps $app
 * @property Companies $defaultCompany
 * @property \Phalcon\Di $di
 */
class Users extends \Baka\Auth\Models\Users
{
    use PermissionsTrait;
    use Billable;
    use SubscriptionPlanLimitTrait;
    use FileSystemModelTrait;
    use HashTableTrait;
    use NotifiableTrait;
    use EventManagerAwareTrait;

    /**
     * Default Company Branch.
     *
     * @var integer
     */
    public $default_company_branch;

    /**
     * Roles id.
     *
     * @var integer
     */
    public $roles_id;

    /**
     * Stripe id.
     *
     * @var string
     */
    public $stripe_id;

    /**
     * Card last four numbers.
     *
     * @var integer
     */
    public $card_last_four;

    /**
     * Card Brand.
     *
     * @var integer
     */
    public $card_brand;

    /**
     * Trial end date.
     *
     * @var string
     */
    public $trial_ends_at;

    /**
     * Provide the app plan id
     * if the user is signing up a new company.
     *
     * @var integer
     */
    public $appPlanId = null;

    /**
     * Active subscription id.Not an actual table field, used temporarily.
     * @var string
     */
    public $active_subscription_id;

    /**
     * System Module Id.
     * @var integer
     */
    public $system_modules_id = 2;

    /**
     * User email activation code.
     *
     * @var string
     */
    public $user_activation_email;

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
        $this->hasOne('default_company', 'Canvas\Models\Companies', 'id', ['alias' => 'defaultCompany']);
        $this->hasOne('default_company', 'Canvas\Models\Companies', 'id', ['alias' => 'currentCompany']);

        $this->hasOne(
            'id',
            'Canvas\Models\UserRoles',
            'users_id',
            ['alias' => 'permission']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserRoles',
            'users_id',
            ['alias' => 'permissions']
        );

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
                    'conditions' => 'Canvas\Models\UserRoles.apps_id = ' . $this->di->getApp()->getId(),
                ]
            ]
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
            'Canvas\Models\UsersAssociatedCompanies',
            'users_id',
            [
                'alias' => 'companies',
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
                'conditions' => 'system_modules_id = ?0',
                'bind' => [$systemModule->getId()]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\FileSystemEntities',
            'entity_id',
            [
                'alias' => 'photo',
                'conditions' => 'system_modules_id = ?0',
                'bind' => [$systemModule->getId()]
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

        $validator->add(
            'displayname',
            new Regex([
                'field' => 'displayname',
                'message' => _('Please use alphanumerics only.'),
                'pattern' => '/^[A-Za-z0-9_-]{1,32}$/',
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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'users';
    }

    /**
    * Set hashtable settings table, userConfig ;).
    *
    * @return void
    */
    private function createSettingsModel(): void
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
     * This only ocurres when signing up the first time, after that all users invites
     * come with a default_company id attached.
     *
     * @return boolean
     */
    public function isFirstSignup(): bool
    {
        return empty($this->default_company);
    }

    /**
     * Does the user have a role assign to him?
     *
     * @return boolean
     */
    public function hasRole(): bool
    {
        return !empty($this->roles_id);
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
     * User login.
     *
     * @param string $email
     * @param string $password
     * @param integer $autologin
     * @param integer $admin
     * @param string $userIp
     * @return Users
     */
    public static function loginApp(string $email, string $password, int $autologin = 1, int $admin, string $userIp) : Users
    {
        //trim email
        $email = ltrim(trim($email));
        $password = ltrim(trim($password));

        //load config
        $config = new stdClass();
        $config->login_reset_time = getenv('AUTH_MAX_AUTOLOGIN_TIME');
        $config->max_login_attempts = getenv('AUTH_MAX_AUTOLOGIN_ATTEMPS');

        //if its a email lets by it by email, if not by displayname
        $user = self::getByEmail($email);

        //first we find the user
        if ($user) {
            // If the last login is more than x minutes ago, then reset the login tries/time
            if ($user->user_last_login_try && $config->login_reset_time && $user->user_last_login_try < (time() - ($config->login_reset_time * 60))) {
                $user->user_login_tries = 0; //turn back to 0 attems, succes
                $user->user_last_login_try = 0;
                $user->update();
            }

            // Check to see if user is allowed to login again... if his tries are exceeded
            if ($user->user_last_login_try && $config->login_reset_time && $config->max_login_attempts && $user->user_last_login_try >= (time() - ($config->login_reset_time * 60)) && $user->user_login_tries >= $config->max_login_attempts) {
                throw new Exception(sprintf(_('You have exhausted all login attempts.'), $config->max_login_attempts));
            }

            //will only work with php.5.5 new password api
            $currentAppUserInfo = $user->getApp([
                'conditions' => 'companies_id = ?0 AND apps_id = ?1',
                'bind' => [$user->currentCompanyId(), Di::getDefault()->getApp()->getId()]
            ]);

            if (!is_object($currentAppUserInfo)) {
                throw new RuntimeException('User not found for this current app');
            }

            if (password_verify($password, trim($currentAppUserInfo->password)) && $user->user_active) {
                //rehas passw if needed
                $options = [
                    //'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM), // Never use a static salt or one that is not randomly generated.
                    'cost' => 12, // the default cost is 10
                ];

                if (password_needs_rehash($password, PASSWORD_DEFAULT, $options)) {
                    $currentAppUserInfo->password = self::passwordHash($password);
                    $currentAppUserInfo->updateOrFail();
                }

                $autologin = (isset($autologin)) ? true : 0;

                $admin = (isset($admin)) ? 1 : 0;

                // Reset login tries
                $user->lastvisit = date('Y-m-d H:i:s');
                $user->user_login_tries = 0;
                $user->user_last_login_try = 0;
                $user->update();

                return $user;
            } // Only store a failed login attempt for an active user - inactive users can't login even with a correct password
            elseif ($user->user_active) {
                // Save login tries and last login
                if ($user->getId() != self::ANONYMOUS) {
                    $user->user_login_tries += 1;
                    $user->user_last_login_try = time();
                    $user->update();
                }

                throw new Exception(_('Invalid Username or Password.'));
            } elseif ($user->isBanned()) {
                throw new Exception(_('User has not been banned, please check your email for the activation link.'));
            } else {
                throw new Exception(_('User has not been activated, please check your email for the activation link.'));
            }
        } else {
            throw new Exception(_('Invalid Username or Password.'));
        }
    }

    /**
     * Strat a free trial.
     *
     * @param Users $user
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

        if (!$subscription->save()) {
            throw new ServerErrorHttpException((string)current($this->getMessages()));
        }

        $this->trial_ends_at = $subscription->trial_ends_at;
        $this->update();

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

        //Assign admin role to the system if we dont get a specify role
        if (!$this->hasRole()) {
            $role = Roles::findFirstByName('Admins');
            $this->roles_id = $role->getId();
        }
    }

    /**
     * What the current company the users is logged in with
     * in this current session?
     *
     * @return integer
     */
    public function currentCompanyId(): int
    {
        return (int) $this->default_company;
    }

    /**
     * What the current company brach the users is logged in with
     * in this current session?
     *
     * @return integer
     */
    public function currentCompanyBranchId(): int
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
        //need to run it here, since we overwirte the default_company id and null this function objective
        $isFirstSignup = $this->isFirstSignup();

        /**
         * if we dont find the userdata di lets create it.
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
     * Get the list of all the associated apps this users has.
     *
     * @return array
     */
    public function getAssociatedApps(): array
    {
        return array_map(function ($apps) {
            return $apps['apps_id'];
        }, $this->getApps(['columns' => 'apps_id', 'group' => 'apps_id'])->toArray());
    }

    /**
     * Get an array of the associates companies Ids.
     *
     * @return array
     */
    public function getAssociatedCompanies(): array
    {
        return array_map(function ($company) {
            return $company['companies_id'];
        }, $this->getCompanies(['columns' => 'companies_id'])->toArray());
    }

    /**
     * Get user by key.
     * @param string $userActivationEmail
     * @return Users
     */
    public static function getByUserActivationEmail(string $userActivationEmail): Users
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
}
