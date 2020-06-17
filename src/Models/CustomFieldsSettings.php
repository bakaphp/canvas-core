<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsSettings extends AbstractModel
{
    public int $custom_fields_id;
    public string $name;
    public ?string $value = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('custom_fields_settings');

        $this->belongsTo(
            'custom_fields_id',
            'Canvas\Models\CustomFields',
            'id',
            ['alias' => 'fields']
        );
    }
}
