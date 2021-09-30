<?php
declare(strict_types=1);

namespace Canvas\Models\Notifications;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Database\ModelInterface;
use Canvas\Models\AbstractModel;
use Canvas\Models\Apps;
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

    /**
     * Get the user entity importance setting.
     *
     * @param Apps $app
     * @param UserInterface $user
     * @param ModelInterface $entity
     *
     * @return self|null
     */
    public static function getByEntity(Apps $app, UserInterface $user, ModelInterface $entity) : ?self
    {
        /**
         * @todo expand to use more system modules
         */
        return self::findFirst([
            'conditions' => 'apps_id = :apps_id: AND users_id = :users_id: AND entity_id = :entity_id:',
            'bind' => [
                'apps_id' => $app->getId(),
                'users_id' => $user->getId(),
                'entity_id' => $entity->getId(),
            ]
        ]);
    }
}
