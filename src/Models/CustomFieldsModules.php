<?php
declare(strict_types=1);

namespace Canvas\Models;

class CustomFieldsModules extends AbstractModel
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
    public $apps_id;

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
