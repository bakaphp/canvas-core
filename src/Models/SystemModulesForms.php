<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class SystemModulesForms extends AbstractModel
{
    public int $companies_id;
    public int $apps_id;
    public int $system_modules_id;
    public string $name;
    public string $slug;
    public string $form_schema;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'system_modules_id',
            'Canvas\Models\SystemModules',
            'id',
            ['alias' => 'systemModule']
        );

        $this->setSource('system_modules_forms');
    }

     /**
     * Before create system modules forms
     *
     * @return void
     */
    public function beforeCreate()
    {
        $this->companies_id = Di::getDefault()->getUserData()->currentCompanyId();
        $this->apps_id = Di::getDefault()->getApp()->getId();
        parent::beforeCreate();
    }

}
