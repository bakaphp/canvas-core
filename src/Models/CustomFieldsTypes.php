<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsTypes extends AbstractModel
{
    public string $name;
    public $description;
    public ?string $icon = null;

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
}
