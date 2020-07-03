<?php
declare(strict_types=1);

namespace Canvas\Models;

class Webhooks extends AbstractModel
{
    public int $system_modules_id;
    public int $apps_id;
    public string $name;
    public string $description;
    public string $action;
    public string  $format;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('webhooks');

        $this->hasMany(
            'id',
            'Canvas\Models\UserWebhooks',
            'webhooks_id',
            ['alias' => 'userWebhook']
        );

        $this->belongsTo(
            'system_modules_id',
            'Canvas\Models\SystemModules',
            'id',
            ['alias' => 'systemModule']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );
    }
}
