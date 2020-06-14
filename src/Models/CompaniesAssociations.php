<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Class CompanyBranches.
 *
 * @package Canvas\Models
 *
 */
class CompaniesAssociations extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $companies_groups_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

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
