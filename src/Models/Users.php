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
     * User email activation code
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
        $this->hasMany('id', 'Baka\Auth\Models\UsersAssociatedCompany', 'users_id', ['alias' => 'companies']);
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
                    'conditions' => 'Canvas\Models\UserRoles.apps_id = ' . $this->di->getConfig()->app->id,
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
            'Canvas\Models\UserWebhooks',
            'users_id',
            ['alias' => 'userWebhook']
        );

        $systemModule = SystemModules::getSystemModuleByModelName(self::class);
        $this->hasMany(
            'id',
            'Canvas\Models\FileSystem',
            'entity_id',
            [
                'alias' => 'files',
                'conditions' => 'system_modules_id = ?0',
                'bind' => [$systemModule->getId()]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\FileSystem',
            'entity_id',
            [
                'alias' => 'logo',
                'conditions' => "system_modules_id = ?0 and file_type in ('png','jpg','bmp','jpeg','webp')",
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
     * Get the User key for redis.
     *
     * @return string
     */
    public function getKey() : string
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
        return empty($this->default_company) ? true : false;
    }

    /**
     * Does the user have a role assign to him?
     *
     * @return boolean
     */
    public function hasRole(): bool
    {
        return !empty($this->roles_id) ? true : false;
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

        /**
         * User signing up for a new app / plan
         * How do we know? well he doesnt have a default_company.
         */
        if ($isFirstSignup) {
            $company = new Companies();
            $company->name = $this->defaultCompanyName;
            $company->users_id = $this->getId();

            if (!$company->save()) {
                throw new Exception((string) current($company->getMessages()));
            }

            $this->default_company = $company->getId();

            if (!$this->update()) {
                throw new ServerErrorHttpException((string) current($this->getMessages()));
            }

            $this->stripe_id = $company->getPaymentGatewayCustomerId();
            $this->default_company_branch = $this->defaultCompany->branch->getId();
            $this->update();

        //update default subscription free trial
            //$company->app->subscriptions_id = $this->startFreeTrial()->getId();
            //$company->update();
        } else {
            //we have the company id
            if (empty($this->default_company_branch)) {
                $this->default_company_branch = $this->defaultCompany->branch->getId();
            }
        }

        //Create new company associated company
        $this->defaultCompany->associate($this, $this->defaultCompany);

        //Insert record into user_roles
        $userRole = new UserRoles();
        $userRole->users_id = $this->id;
        $userRole->roles_id = $this->roles_id;
        $userRole->apps_id = $this->di->getApp()->getId();
        $userRole->companies_id = $this->default_company;

        if (!$userRole->save()) {
            throw new ServerErrorHttpException((string)current($userRole->getMessages()));
        }

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
     * Get user by key
     * @param string $userActivationEmail
     * @return Users
     */
    public static function getByUserActivationEmail(string $userActivationEmail): Users
    {
        return self::findFirst([
            'conditions' => 'key = ?0 and user_active =?1 and is_deleted = 0',
            'bind' => [$userActivationEmail, 1],
        ]);
    }
}
