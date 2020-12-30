<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Blameable\Blameable;
use Baka\Blameable\BlameableTrait;
use Baka\Contracts\Database\HashTableTrait;
use Baka\Contracts\EventsManager\EventManagerAwareTrait;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Traits\FileSystemModelTrait;
use Canvas\Traits\UsersAssociatedTrait;
use Carbon\Carbon;
use Exception;
use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class Companies extends AbstractModel
{
    use HashTableTrait;
    use UsersAssociatedTrait;
    use FileSystemModelTrait;
    use BlameableTrait;
    use EventManagerAwareTrait;

    public int $users_id;
    public ?int $has_activities = 0;
    public ?int $appPlanId = null;
    public ?int $currency_id = 0;
    public ?string $language = null;
    public ?string $timezone = null;
    public ?string $currency = null;
    public int $system_modules_id = 1;
    public ?string $phone = null;

    const DEFAULT_COMPANY = 'DefaulCompany';
    const DEFAULT_COMPANY_APP = 'DefaulCompanyApp_';
    const PAYMENT_GATEWAY_CUSTOMER_KEY = 'payment_gateway_customer_id';
    const DEFAULT_COMPANY_BRANCH_APP = 'DefaultCompanyBranchApp_';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies');

        $this->keepSnapshots(true);
        $this->addBehavior(new Blameable());

        $this->hasMany('id', 'Baka\Auth\Models\CompanySettings', 'id', ['alias' => 'settings']);

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CompaniesBranches',
            'companies_id',
            ['alias' => 'branches']
        );

        $this->hasOne(
            'id',
            'Canvas\Models\CompaniesBranches',
            'companies_id',
            [
                'alias' => 'defaultBranch',
                'params' => [
                    'conditions' => 'is_default = 1'
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CompaniesCustomFields',
            'companies_id',
            ['alias' => 'fields']
        );

        $this->hasMany(
            'id',
            'Canvas\CustomFields\CustomFields',
            'companies_id',
            ['alias' => 'custom-fields']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedCompanies',
            'companies_id',
            ['alias' => 'UsersAssociatedCompanies']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'companies_id',
            ['alias' => 'UsersAssociatedApps']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'companies_id',
            [
                'alias' => 'UsersAssociatedByApps',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId()
                ]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\CompaniesBranches',
            'companies_id',
            [
                'alias' => 'branch',
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\UserCompanyApps',
            'companies_id',
            [
                'alias' => 'app',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId()
                ]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\UserCompanyApps',
            'companies_id',
            [
                'alias' => 'apps',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId()
                ]
            ]
        );

        $this->hasMany(
            'id',
            CompaniesAssociations::class,
            'companies_id',
            ['alias' => 'companiesAssoc']
        );

        //users associated with this company app
        $this->hasManyToMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'companies_id',
            'users_id',
            'Canvas\Models\Users',
            'id',
            [
                'alias' => 'users',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId() . ' AND Canvas\Models\UsersAssociatedApps.is_deleted = 0',
                ]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\Subscription',
            'companies_id',
            [
                'alias' => 'subscription',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId() . ' AND is_deleted = 0',
                    'order' => 'id DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\Subscription',
            'companies_id',
            [
                'alias' => 'subscriptions',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId() . ' AND is_deleted = 0',
                    'order' => 'id DESC'
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'companies_id',
            ['alias' => 'user-webhooks']
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
    }

    /**
     * Model validation.
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'name',
            new PresenceOf([
                'model' => $this,
                'required' => true,
            ])
        );

        return $this->validate($validator);
    }

    /**
     * Register a company given a user and name.
     *
     * @param  Users  $user
     * @param  string $name
     *
     * @return Companies
     */
    public static function register(Users $user, string $name) : Companies
    {
        $company = new self();
        $company->name = $name;
        $company->users_id = $user->getId();
        $company->saveOrFail();

        return $company;
    }

    /**
     * Confirm if a user belongs to this current company.
     *
     * @param Users $user
     *
     * @return bool
     */
    public function userAssociatedToCompany(Users $user) : bool
    {
        return $this->countUsersAssociatedApps('users_id =' . $user->getId() . ' and apps_id = ' . Di::getDefault()->get('app')->getId()) > 0;
    }

    /**
     * Get the stripe customer id from the.
     *
     * @return ?string
     */
    public function getPaymentGatewayCustomerId() : ?string
    {
        return $this->get(self::PAYMENT_GATEWAY_CUSTOMER_KEY);
    }

    /**
     * Before crate company.
     *
     * @return void
     */
    public function beforeCreate()
    {
        parent::beforeCreate();

        $this->language = $this->di->get('app')->get('language');
        $this->timezone = $this->di->get('app')->get('timezone');
        $this->currency_id = Currencies::findFirstByCode($this->di->get('app')->get('currency'))->getId();
    }

    /**
     * After creating the company.
     *
     * @return void
     */
    public function afterCreate()
    {
        $this->fire('company:afterSignup', $this);
    }

    /**
     * Get the default company the users has selected.
     *
     * @param  Users  $user
     *
     * @return Companies
     */
    public static function getDefaultByUser(Users $user) : Companies
    {
        //verify the user has a default company
        $defaultCompany = $user->get(self::cacheKey());

        //found it
        if (!is_null($defaultCompany)) {
            return self::findFirst($defaultCompany);
        }

        //second try
        $defaultCompany = UsersAssociatedCompanies::findFirst([
            'conditions' => 'users_id = ?0 and user_active =?1',
            'bind' => [$user->getId(), 1],
        ]);

        if (is_object($defaultCompany)) {
            return self::findFirst($defaultCompany->companies_id);
        }

        throw new Exception(_("User doesn't have an active company"));
    }

    /**
     * Start a free trial for a new company.
     *
     * @return string //the customer id
     */
    public function startFreeTrial() : ?string
    {
        $defaultPlan = AppsPlans::getDefaultPlan();
        $trialEndsAt = Carbon::now()->addDays($this->di->get('app')->plan->free_trial_dates);

        //Lets create a new default subscription without payment method
        $this->user->newSubscription($defaultPlan->name, $defaultPlan->stripe_id, $this, $this->di->getApp())
                ->trialDays($defaultPlan->free_trial_dates)
                ->create();

        //ook for the subscription and update the missing info
        $subscription = $this->subscription;
        $subscription->apps_plans_id = $this->di->get('app')->default_apps_plan_id;
        $subscription->trial_ends_days = $trialEndsAt->diffInDays(Carbon::now());
        $subscription->is_freetrial = 1;
        $subscription->is_active = 1;
        $subscription->payment_frequency_id = 1;

        if (!$subscription->save()) {
            throw new InternalServerErrorException((string) 'Subscription for new company couldnt be created ' . current($this->getMessages()));
        }

        return $this->user->stripe_id;
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
        //parent::afterSave();
        $this->associateFileSystem();
    }

    /**
     * Get an array of the associates users_id for this company.
     *
     * @return array
     */
    public function getAssociatedUsersByApp() : array
    {
        /**
         * @todo move to use the users relationship
         */
        return array_map(function ($users) {
            return $users['users_id'];
        }, $this->getUsersAssociatedByApps([
            'columns' => 'users_id',
            'conditions' => 'user_active = 1'
        ])->toArray());
    }

    /**
     * Overwrite the relationship.
     *
     * @return void
     */
    public function getLogo()
    {
        return $this->getFileByName('logo');
    }

    /**
     * Get the default company key for the current app
     * this is use to store in redis the default company id for the current
     * user in session every time they switch between companies on the diff apps.
     *
     * @return string
     */
    public static function cacheKey() : string
    {
        return self::DEFAULT_COMPANY_APP . Di::getDefault()->get('app')->getId();
    }

    /**
     * Get the default company key for the current app
     * this is use to store in redis the default company id for the current
     * user in session every time they switch between companies on the diff apps.
     *
     * @return string
     */
    public function branchCacheKey() : string
    {
        return self::DEFAULT_COMPANY_BRANCH_APP . $this->getDI()->get('app')->getId() . '_' . $this->getId();
    }

    /**
     * Given a user remove it from this app company.
     *
     * @param Users $user
     *
     * @return void
     */
    public function deactiveUser(Users $user)
    {
        //deactive the user from a company app, not delete
    }

    /**
     * Given the user reactive it for this app company.
     *
     * @param Users $user
     *
     * @return void
     */
    public function reactiveUser(Users $user)
    {
        //reactive the user from a company app, not delete
    }
}
