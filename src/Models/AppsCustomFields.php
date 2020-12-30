<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\CustomFields\AppsCustomFields as BakaAppsCustomFields;

class AppsCustomFields extends BakaAppsCustomFields
{
    /**
     * Initialize some stuff.
     *
     * @return void
     */
    public function initialize()
    {
        $this->setSource('apps_custom_fields');
    }
}
