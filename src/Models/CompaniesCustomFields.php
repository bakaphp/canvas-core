<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Models\AbstractModel;

class CompaniesCustomFields extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var integer
     */
    public $custom_fields_id;

    /**
     *
     * @var string
     */
    public $value;

    /**
     *
     * @var integer
     */
    public $is_deleted;

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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('companies_custom_fields');

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'custom_fields_id',
            'Canvas\CustomFields\CustomFields',
            'id',
            ['alias' => 'fields']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'companies_custom_fields';
    }

    /**
     * Set the custom primary field id
     *
     * @param int $id
     */
    public function setCustomId(int $id)
    {
        $this->companies_id = $id;
    }
}
