<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Canvas\Models\AbstractModel;
use Canvas\Models\SystemModules;

class UserEntityImportance extends AbstractModel
{
    public int $apps_id;
    public string $name;
    public string $entity_id;
    public int $system_modules_id;
    public int $importance_id;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_notification_entity_importance');

        $this->belongsTo(
            'importance_id',
            Importance::class,
            'id',
            [
                'alias' => 'importance'
            ]
        );

        $this->belongsTo(
            'system_modules_id',
            SystemModules::class,
            'id',
            [
                'alias' => 'systemModule'
            ]
        );
    }
}
