<?php
declare(strict_types=1);

namespace Canvas\Models;

class CompaniesAssociations extends AbstractModel
{
    public int $companies_groups_id;
    public int $companies_id;
    public ?int $is_default = 0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_associations');

        $this->belongsTo(
            'companies_id',
            Companies::class,
            'id',
            ['alias' => 'companies']
        );

        $this->belongsTo(
            'companies_groups_id',
            CompaniesGroups::class,
            'id',
            ['alias' => 'companiesGroups']
        );
    }
}
