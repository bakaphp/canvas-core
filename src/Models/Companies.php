<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Blameable\Blameable;
use Baka\Blameable\BlameableTrait;
use Baka\Contracts\Database\HashTableTrait;
use Baka\Contracts\EventsManager\EventManagerAwareTrait;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Contracts\FileSystemModelTrait;
use Canvas\Contracts\UsersAssociatedTrait;
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
    const GLOBAL_COMPANIES_ID = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies');

        $this->keepSnapshots(true);
        $this->addBehavior(new Blameable());

        $this->hasMany('id', CompaniesSettings::class, 'companies_id', ['alias' => 'settings', 'reusable' => true]);

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user', 'reusable' => true, ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CompaniesBranches',
            'companies_id',
            ['alias' => 'branches', 'reusable' => true, ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\CompaniesBranches',
            'companies_id',
            [
                'alias' => 'defaultBranch',
                'reusable' => true,
                'params' => [
                    'conditions' => 'is_default = 1'
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CompaniesCustomFields',
            'companies_id',
            ['alias' => 'fields', 'reusable' => true, ]
        );

        $this->hasMany(
            'id',
            'Canvas\CustomFields\CustomFields',
            'companies_id',
            ['alias' => 'custom-fields', 'reusable' => true, ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedCompanies',
            'companies_id',
            ['alias' => 'UsersAssociatedCompanies', 'reusable' => true, ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'companies_id',
            ['alias' => 'UsersAssociatedApps', 'reusable' => true, ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UsersAssociatedApps',
            'companies_id',
            [
                'alias' => 'UsersAssociatedByApps',
                'reusable' => true,
                'params' => [
                    'conditions' => "apps_id = {$this->di->get('app')->getId()} and is_deleted = 0"
                ]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\CompaniesBranches',
            'companies_id',
            [
                'alias' => 'branch',
                'reusable' => true,
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\UserCompanyApps',
            'companies_id',
            [
                'alias' => 'app',
                'reusable' => true,
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
                'reusable' => true,
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId()
                ]
            ]
        );

        $this->hasMany(
            'id',
            CompaniesAssociations::class,
            'companies_id',
            ['alias' => 'companiesAssoc', 'reusable' => true, ]
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
                'reusable' => true,
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId() . ' AND Canvas\Models\UsersAssociatedApps.is_deleted = 0',
                ]
            ]
        );

        $this->hasManyToMany(
            'id',
            'Canvas\Models\CompaniesAssociations',
            'companies_id',
            'companies_groups_id',
            'Canvas\Models\CompaniesGroups',
            'id',
            [
                'alias' => 'companyGroups',
                'reusable' => true,
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'companies_id',
            ['alias' => 'user-webhooks', 'reusable' => true, ]
        );

        $systemModule = SystemModules::getByModelName(self::class);
        $this->hasOne(
            'id',
            'Canvas\Models\FileSystemEntities',
            'entity_id',
            [
                'alias' => 'files',
                'reusable' => true,
                'params' => [
                    'conditions' => 'system_modules_id = ?0',
                    'bind' => [$systemModule->getId()]
                ]
            ]
        );
    }

    /**
     * Get the subscription from company group
     * used for relationship.
     *
     * @return Subscription
     *
     * @deprecated v0.3
     */
    public function getSubscription() : ?Subscription
    {
        /**
         * @todo Frontend needs to all relationship if its a subscription app if not, ignore
         * backend doesn't need to handle this logic on the model
         */
        if ($this->di->get('app')->subscriptionBased()) {
            return $this->getDefaultCompanyGroup()->subscription();
        }

        return null;
    }

    /**
     * Get the default company group for this company on the current app.
     *
     * @return CompaniesGroups
     */
    public function getDefaultCompanyGroup() : CompaniesGroups
    {
        $companyGroup = $this->getCompanyGroups([
            'conditions' => 'Canvas\Models\CompaniesGroups.is_default = 1',
            'limit' => 1
        ])->getFirst();

        if (!$companyGroup) {
            throw new InternalServerErrorException('No default Company Group for Company - ' . $this->getId());
        }

        return $companyGroup;
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
        return $this->countUsersAssociatedApps('users_id =' . $user->getId() . ' and apps_id = ' . Di::getDefault()->get('app')->getId() . ' and is_deleted = 0') > 0;
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
            'conditions' => 'is_deleted = 0'
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

    /**
     * Create a branch for this company.
     *
     * @param string|null $name
     *
     * @return CompaniesBranches
     */
    public function createBranch(?string $name = null) : CompaniesBranches
    {
        return  CompaniesBranches::findFirstOrCreate(
            [
                'conditions' => 'companies_id = :companies_id: AND users_id = :users_id: AND name = :name:',
                'bind' => [
                    'companies_id' => $this->getId(),
                    'users_id' => $this->user->getId(),
                    'name' => empty($name) ? $this->name : $name
                ]
            ],
            [
                'companies_id' => $this->getId(),
                'users_id' => $this->user->getId(),
                'name' => empty($name) ? $this->name : $name,
                'is_default' => 1
            ]
        );
    }

    /**
     * Register this company to the the following app.
     *
     * @param Apps $app
     *
     * @return bool
     */
    public function registerInApp(Apps $app) : bool
    {
        $companyApps = new UserCompanyApps();
        $companyApps->companies_id = $this->getId();
        $companyApps->apps_id = $app->getId();
        $companyApps->created_at = date('Y-m-d H:i:s');
        $companyApps->is_deleted = 0;

        return $companyApps->saveOrFail();
    }
}
