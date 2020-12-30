<?php
declare(strict_types=1);

namespace Canvas\Models;

class EmailTemplatesVariables extends AbstractModel
{
    public int $companies_id;
    public int $apps_id;
    public int $system_modules_id;
    public int $users_id;
    public string $name;
    public string $value;

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
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'system_modules_id',
            'Canvas\Models\SystemModules',
            'id',
            ['alias' => 'systemModule']
        );

        $this->setSource('email_templates_variables');
    }
}
