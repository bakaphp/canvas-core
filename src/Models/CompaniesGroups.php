<?php

declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Cashier\Billable;

class CompaniesGroups extends AbstractModel
{
    use Billable;

    public string $name;
    public int $apps_id;
    public int $users_id;
    public ?string $stripe_id = null;
    public ?int $is_default = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_groups');

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
}
