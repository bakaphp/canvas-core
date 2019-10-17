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
class CompaniesGroups extends AbstractModel
{
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
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $users_id;

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

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'companies_groups';
    }
}
