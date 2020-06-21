<?php
declare(strict_types=1);

namespace Canvas\Models;

class AppsPlansSettings extends AbstractModel
{
    public int $apps_plans_id;
    public int $apps_id;
    public string $key;
    public $value;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps_plans_settings');

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );
    }
}
