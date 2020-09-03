<?php
declare(strict_types=1);

namespace Canvas\Models;

class AppsSettings extends AbstractModel
{
    public int $apps_id;
    public string $name;
    public string $value;

    /**
     * Default number of settings for an app.
     */
    const APP_DEFAULT_SETTINGS_NUMBER = 18;

    /**
     * Sub based app key word.
     */
    const SUBSCRIPTION_BASED = 'subscription_based';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps_settings');

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );
    }
}
