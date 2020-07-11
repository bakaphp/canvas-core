<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsModules extends AbstractModel
{
    public int $apps_id;
    public string $name;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('custom_fields_modules');

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->hasMany(
            'id',
            'Canvas\CustomFields\CustomFields',
            'custom_fields_modules_id',
            ['alias' => 'fields']
        );

        // $this->belongsTo(
        //     'apps_id',
        //     'Canvas\Models\Apps',
        //     'id',
        //     ['alias' => 'app']
        // );
    }
}
