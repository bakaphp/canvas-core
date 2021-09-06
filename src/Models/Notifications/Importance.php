<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Canvas\Models\AbstractModel;

class Importance extends AbstractModel
{
    public int $apps_id;
    public string $name;
    public string $validation_expression;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notifications_importance');

        $this->hasMany(
            'id',
            UserEntityImportance::class,
            'importance_id',
            [
                'alias' => 'userImportance'
            ]
        );
    }
}
