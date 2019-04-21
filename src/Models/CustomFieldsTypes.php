<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsTypes extends AbstractModel
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
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $icon;

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
        $this->setSource('custom_fields_types');

        $this->hasMany(
            'id',
            'Canvas\Models\CustomFieldsTypesSettings',
            'custom_fields_types_id',
            ['alias' => 'typesSetting']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'custom_fields_types';
    }
}
