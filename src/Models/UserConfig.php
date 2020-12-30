<?php
declare(strict_types=1);

namespace Canvas\Models;

class UserConfig extends \Baka\Auth\Models\UserConfig
{
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('user_config');
    }
}
