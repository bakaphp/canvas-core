<?php

declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Cashier\Billable;
use Canvas\Models\Behaviors\Uuid;

class CompaniesGroups extends AbstractModel
{
    use Billable;

    public string $name;
    public int $apps_id;
    public int $users_id;
    public ?string $stripe_id = null;
    public ?int $is_default = 0;
    public ?string $country_code = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_groups');
        $this->addBehavior(new Uuid());

        $this->hasMany(
            'id',
            CompaniesAssociations::class,
            'companies_groups_id',
            ['alias' => 'companiesAssoc', 'reusable' => true]
        );

        $this->hasManyToMany(
            'id',
            CompaniesAssociations::class,
            'companies_groups_id',
            'companies_id',
            Companies::class,
            'id',
            ['alias' => 'companies', 'reusable' => true]
        );

        $this->hasManyToMany(
            'id',
            CompaniesAssociations::class,
            'companies_groups_id',
            'companies_id',
            Companies::class,
            'id',
            [
                'alias' => 'defaultCompany',
                'reusable' => true,
                'params' => [
                    'conditions' => 'is_default = 1'
                ]
            ]
        );

        $this->hasOne(
            'id',
            'Canvas\Models\Subscription',
            'companies_groups_id',
            [
                'alias' => 'subscription',
                'reusable' => true,
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId() . ' AND is_deleted = 0',
                    'order' => 'id DESC'
                ]
            ]
        );

        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            [
                'alias' => 'users',
                'reusable' => true
            ]
        );
    }

    /**
     * Associate a company to this company Group.
     *
     * @param Companies $company
     * @param int $isDefault
     *
     * @return CompaniesAssociations
     */
    public function associate(Companies $company, int $isDefault = 1) : CompaniesAssociations
    {
        $companiesAssoc = new CompaniesAssociations();
        $companiesAssoc->companies_id = $company->getId();
        $companiesAssoc->companies_groups_id = $this->getId();
        $companiesAssoc->is_default = $isDefault;
        $companiesAssoc->saveOrFail();

        return $companiesAssoc;
    }
}
