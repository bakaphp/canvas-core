<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;
use Phalcon\Di;

class NotificationType extends AbstractModel
{
    public int $apps_id;
    public int $system_modules_id;
    public string $name;
    public string $key;
    public ?string $description = null;
    public ?string $template = null;
    public ?string $icon_url = null;
    public int $with_realtime;
    public int $parent_id = 0;
    public int $is_published = 1;
    public float $weight = 0.0;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notification_types');
    }

    /**
     * Get the notification by its key
     *  by default in any kanvas app the key will be its classnames.
     *
     * @param string $key
     *
     * @return void
     */
    public static function getByKey(string $key) : NotificationType
    {
        $app = Di::getDefault()->getApp();

        return self::findFirstOrFail([
            'conditions' => 'apps_id in (?0, ?1) AND key = ?2',
            'bind' => [
                $app->getId(),
                Apps::CANVAS_DEFAULT_APP_ID,
                $key
            ]
        ]);
    }

    /**
     * Get notification by its key or create it.
     *
     * @param string $key
     * @param Model $model
     *
     * @return NotificationType
     */
    public static function getByKeyOrCreate(string $key, ?Model $model = null) : NotificationType
    {
        $app = Di::getDefault()->getApp();
        $systemModule = SystemModules::getByModelName(get_class($model));

        return self::findFirstOrCreate(
            [
                'conditions' => 'apps_id in (?0, ?1) AND key = ?2',
                'bind' => [
                    $app->getId(),
                    Apps::CANVAS_DEFAULT_APP_ID,
                    $key
                ]
            ],
            [
                'apps_id' => $app->getId(),
                'system_modules_id' => $systemModule->getId(),
                'name' => get_class($model),
                'key' => $key,
                'with_realtime' => 0
            ]
        );
    }
}
