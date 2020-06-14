<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsValues extends AbstractModel
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
    public $custom_fields_id;

    /**
     *
     * @var string
     */
    public $label;

    /**
     *
     * @var string
     */
    public $value;

    /**
     *
     * @var integer
     */
    public $is_default;

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
        $this->setSource('custom_fields_values');

        $this->belongsTo(
            'custom_fields_id',
            'Canvas\Models\CustomFields',
            'id',
            ['alias' => 'field']
        );
    }

}
