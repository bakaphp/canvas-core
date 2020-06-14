<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Models\Apps;

class AppsSettings extends AbstractModel
{
    /**
     * Default number of settings for an app.
     */
    const APP_DEFAULT_SETTINGS_NUMBER = 16;
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
     * @var string
     */
    public $value;

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
     *
     * @var integer
     */
    public $is_deleted;

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
