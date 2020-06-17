<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsValues extends AbstractModel
{
    public int $custom_fields_id;
    public string $label;
    public ?string $value = null;
    public int $is_default;

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
