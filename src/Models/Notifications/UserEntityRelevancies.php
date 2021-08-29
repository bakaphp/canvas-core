<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Canvas\Models\AbstractModel;

class UserEntityRelevancies extends AbstractModel
{
    public int $apps_id;
    public string $name;
    public string $entity_id;
    public int $system_modules_id;
    public int $relevancies_id;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_notification_entity_relevancies');
    }
}
