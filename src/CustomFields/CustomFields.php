<?php
declare(strict_types=1);

namespace Canvas\CustomFields;

use Canvas\Models\AbstractModel;

class CustomFields extends AbstractModel
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
    public $users_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $custom_fields_modules_id;

    /**
     *
     * @var integer
     */
    public $fields_type_id;

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
        $this->setSource('custom_fields');

        $this->belongsTo(
            'custom_fields_modules_id',
            'Canvas\Models\CustomFieldsModules',
            'id',
            ['alias' => 'modules']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CompanyCustomFields',
            'custom_field_id',
            ['alias' => 'company-fields']
        );

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'companies']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CustomFieldsSettings',
            'custom_fields_id',
            ['alias' => 'fields-settings']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\CustomFieldsValues',
            'custom_fields_id',
            ['alias' => 'fields-values']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields';
    }
}
