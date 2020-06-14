<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class NotificationType extends AbstractModel
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
    *
    * @var integer
    */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $system_modules_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $key;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $template;

    /**
     *
     * @var string
     */
    public $icon_url;

    /**
     *
     * @var int
     */
    public $with_realtime;

    /**
     *
     * @var integer
     */
    public $is_deleted;

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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notification_types');
    }

    /**
     * Get the notification by its key
     *  by defautl in any kanvas app the key will be its classname
     *
     * @param string $key
     * @return void
     */
    public static function getByKey(string $key): NotificationType
    {
        $app = Di::getDefault()->getApp();

        return self::findFirstOrFail([
            'conditions' => 'apps_id in (?0, ?1) AND key = ?2',
            'bind' => [$app->getId(), Apps::CANVAS_DEFAULT_APP_ID, $key]
        ]);
    }
}
