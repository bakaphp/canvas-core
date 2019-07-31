<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Canvas\Exception\ServerErrorHttpException;
use Exception;
use Carbon\Carbon;
use Baka\Database\Contracts\HashTableTrait;
use Baka\Blameable\BlameableTrait;
use Canvas\Traits\UsersAssociatedTrait;
use Canvas\Traits\FileSystemModelTrait;
use Baka\Blameable\Blameable;
use Phalcon\Traits\EventManagerAwareTrait;

/**
 * Class Companies.
 *
 * @package Canvas\Models
 *
 * @property Users $user
 * @property Users $userData
 * @property DefaultCompany $default_company
 * @property CompaniesBranches $branch
 * @property CompaniesBranches $branches
 * @property Subscription $subscription
 * @property Config $config
 * @property UserCompanyApps $app
 * @property \Phalcon\Di $di
 * @property Roles $roles_id
 */
class Companies extends \Canvas\CustomFields\AbstractCustomFieldsModel
{
    use HashTableTrait;
    use UsersAssociatedTrait;
    use FileSystemModelTrait;
    use BlameableTrait;
    use EventManagerAwareTrait;

    const DEFAULT_COMPANY = 'DefaulCompany';
    const PAYMENT_GATEWAY_CUSTOMER_KEY = 'payment_gateway_customer_id';

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
    public $profile_image;

    /**
     *
     * @var string
     */
    public $website;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $has_activities;

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
     * Provide the app plan id.
     *
     * @var integer
     */
    public $appPlanId = null;

    /**
     *
     * @var integer
     */
    public $currency_id;

    /**
     *
     * @var string
     */
    public $language;

    /**
     *
     * @var string
     */
    public $timezone;

    /**
     *
     * @var string
     */
    public $currency;

    /**
     *
     * @var integer
     */
    public $system_modules_id = 1;

    /**
     *
     * @var string
     */
    public $phone;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies');

        $this->keepSnapshots(true);
        $this->addBehavior(new Blameable());

        $this->belongsTo('users_id', 'Baka\Auth\Models\Users', 'id', ['alias' => 'user']);
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
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId()
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
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId()
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
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId()
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
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId() . '  AND is_deleted = 0 ',
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
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId() . ' AND is_deleted = 0',
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
                'conditions' => 'system_modules_id = ?0',
                'bind' => [$systemModule->getId()]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\FileSystemEntities',
            'entity_id',
            [
                'alias' => 'logo',
                'conditions' => 'system_modules_id = ?0',
                'bind' => [$systemModule->getId()]
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
    * @return Companies
    */
    public static function register(Users $user, string $name): Companies
    {
        $company = new self();
        $company->name = $name;
        $company->users_id = $user->getId();

        if (!$company->save()) {
            throw new Exception(current($company->getMessages()));
        }

        return $company;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'companies';
    }

    /**
     * Confirm if a user belongs to this current company.
     *
     * @param Users $user
     * @return boolean
     */
    public function userAssociatedToCompany(Users $user): bool
    {
        return $this->countUsersAssociatedCompanies('users_id =' . $user->getId()) > 0;
    }

    /**
     * Get the stripe customer id from the.
     *
     * @return ?string
     */
    public function getPaymentGatewayCustomerId(): ?string
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

        $this->language = $this->di->getApp()->get('language');
        $this->timezone = $this->di->getApp()->get('timezone');
        $this->currency_id = Currencies::findFirstByCode($this->di->getApp()->get('currency'))->getId();
    }

    /**
     * After creating the company.
     *
     * @return void
     */
    public function afterCreate()
    {
        parent::afterCreate();

        $this->fire('company:afterSignup', $this);
    }

    /**
     * Get the default company the users has selected.
     *
     * @param  Users  $user
     * @return Companies
     */
    public static function getDefaultByUser(Users $user): Companies
    {
        //verify the user has a default company
        $defaultCompany = UserConfig::findFirst([
            'conditions' => 'users_id = ?0 and name = ?1',
            'bind' => [$user->getId(), self::DEFAULT_COMPANY],
        ]);

        //found it
        if (is_object($defaultCompany)) {
            return self::findFirst($defaultCompany->value);
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
        $trialEndsAt = Carbon::now()->addDays($this->di->getApp()->plan->free_trial_dates);

        //Lets create a new default subscription without payment method
        $this->user->newSubscription($defaultPlan->name, $defaultPlan->stripe_id, $this, $this->di->getApp())
                ->trialDays($defaultPlan->free_trial_dates)
                ->create();

        //ook for the subscription and update the missing info
        $subscription = $this->subscription;
        $subscription->apps_plans_id = $this->di->getApp()->default_apps_plan_id;
        $subscription->trial_ends_days = $trialEndsAt->diffInDays(Carbon::now());
        $subscription->is_freetrial = 1;
        $subscription->is_active = 1;
        $subscription->payment_frequency_id = 1;

        if (!$subscription->save()) {
            throw new ServerErrorHttpException((string)'Subscription for new company couldnt be created ' . current($this->getMessages()));
        }

        return $this->user->stripe_id;
    }

    /**
     * Upload Files.
     *
     * @todo move this to the baka class
     * @return void
     */
    public function afterSave()
    {
        //parent::afterSave();
        $this->associateFileSystem();
    }

    /**
    * Get an array of the associates users for this company.
    *
    * @return array
    */
    public function getAssociatedUsersByApp(): array
    {
        return array_map(function ($users) {
            return $users['users_id'];
        }, $this->getUsersAssociatedByApps(['columns' => 'users_id'])->toArray());
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
}
