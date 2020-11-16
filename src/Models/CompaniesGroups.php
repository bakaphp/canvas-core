<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Cashier\Billable;

/**
 * Class CompanyBranches.
 *
 * @package Canvas\Models
 *
 */
class CompaniesGroups extends AbstractModel
{
    use Billable;

    public string $name;
    public int $apps_id;
    public int $users_id;
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
            ['alias' => 'companiesAssoc']
        );

        $this->hasManyToMany(
            'id',
            CompaniesAssociations::class,
            'companies_groups_id',
            'companies_id',
            Companies::class,
            'id',
            ['alias' => 'companies']
        );

        $this->hasOne(
            'id',
            'Canvas\Models\Subscription',
            'companies_groups_id',
            [
                'alias' => 'subscription',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->get('app')->getId() . ' AND is_deleted = 0',
                    'order' => 'id DESC'
                ]
            ]
        );
    }
}
