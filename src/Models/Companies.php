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

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies');

        $this->keepSnapshots(true);
        $this->addBehavior(new Blameable());

        $this->hasMany('id', CompaniesSettings::class, 'id', ['alias' => 'settings']);

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
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId() . ' AND Canvas\Models\UsersAssociatedApps.is_deleted = 0',
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
            ]
        );

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'companies_id',
            ['alias' => 'user-webhooks']
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
                    'bind' => [$systemModule->getId()]
                ]
            ]
        );
    }

    /**
     * Get the default company group for this company on the current app.
     *
     * @return CompaniesGroups
     */
    public function getDefaultCompanyGroup() : CompaniesGroups
    {
        $companyGroup = $this->getCompanyGroups([
            'conditions' => 'Canvas\Models\CompaniesGroups.apps_id = :apps_id: AND Canvas\Models\CompaniesGroups.is_default = 1',
            'bind' => [
                'apps_id' => Di::getDefault()->get('app')->getId()
            ],
            'limit' => 1
        ]);

        if (empty($companyGroup)) {
            throw new InternalServerErrorException('No default Company Group Found');
        }

        return $companyGroup[0];
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
        return $this->countUsersAssociatedCompanies('users_id =' . $user->getId()) > 0;
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
     * user in session everytime they switch between companies on the diff apps.
     *
     * @return string
     */
    public static function cacheKey() : string
    {
        return self::DEFAULT_COMPANY_APP . Di::getDefault()->getApp()->getId();
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
