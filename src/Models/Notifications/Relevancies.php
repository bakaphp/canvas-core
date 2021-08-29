<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Canvas\Models\AbstractModel;

class Relevancies extends AbstractModel
{
    public int $apps_id;
    public string $name;
    public string $validation_expression;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notifications_relevancies');
    }
}
