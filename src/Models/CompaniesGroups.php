<?php
declare(strict_types=1);

namespace Canvas\Models;

/**
 * Class CompanyBranches.
 *
 * @package Canvas\Models
 *
 */
class CompaniesGroups extends AbstractModel
{
    public string $name;
    public int $apps_id;
    public int $users_id;

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
    }
}
