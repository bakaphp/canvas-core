<?php
declare(strict_types=1);

namespace Canvas\CustomFields;

use Baka\Database\CustomFields\CustomFields as BakaCustomFields;
use Canvas\Models\CustomFieldsValues;

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

        $this->hasMany(
            'id',
            'Canvas\Models\CustomFieldsValues',
            'custom_fields_id',
            ['alias' => 'values']
        );
    }

    /**
     * Given an array of values, add it to this custom fields
     * related module.
     *
     * @param array $values
     *
     * @return bool
     */
    public function addValues(array $values) : bool
    {
        if ($this->values) {
            $this->values->delete();
        }

        foreach ($values as $key => $value) {
            $customFieldsValue = new CustomFieldsValues();
            $customFieldsValue->custom_fields_id = $this->getId();
            $customFieldsValue->label = $value;
            $customFieldsValue->value = $key;
            $customFieldsValue->saveOrFail();
        }

        return true;
    }
}
