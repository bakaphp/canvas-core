<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsTypesSettings extends AbstractModel
{
    public int $custom_fields_types_id;
    public string $name;

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
