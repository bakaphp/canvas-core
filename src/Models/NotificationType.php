<?php
declare(strict_types=1);

namespace Canvas\Models;

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
}
