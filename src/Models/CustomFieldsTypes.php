<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\CustomFields\FieldsType;

class CustomFieldsTypes extends FieldsType
{

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
