<?php
declare(strict_types=1);

namespace Canvas\CustomFields;

use  Baka\Database\CustomFields\CustomFields as BakaCustomFields;

class CustomFields extends BakaCustomFields
{
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

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
}
