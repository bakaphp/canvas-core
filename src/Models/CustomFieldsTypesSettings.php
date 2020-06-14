<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsTypesSettings extends AbstractModel
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
    public $custom_fields_types_id;

    /**
     *
     * @var string
     */
    public $name;

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
        $this->setSource('custom_fields_types_settings');

        $this->belongsTo(
            'custom_fields_types_id',
            'Canvas\Models\CustomFieldsTypes',
            'id',
            ['alias' => 'fieldsType']
        );
    }

}
