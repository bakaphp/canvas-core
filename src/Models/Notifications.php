<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class Notifications extends AbstractModel
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
    public $from_users_id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

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
     * @var integer
     */
    public $notification_type_id;

    /**
     *
     * @var integer
     */
    public $entity_id;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var integer
     */
    public $read;

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
        $this->setSource('notifications');

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'from_users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'from']
        );

        $this->belongsTo(
            'notification_type_id',
            'Canvas\Models\NotificationType',
            'id',
            ['alias' => 'type']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'notifications';
    }

    /**
     * Mark as Read all the notification from a user.
     *
     * @param Users $user
     * @return void
     */
    public static function markAsRead(Users $user): bool
    {
        $result = Di::getDefault()->getDb()->prepare(
            'UPDATE notifications set `read` = 1 WHERE users_id = ? AND companies_id = ? AND apps_id = ?'
        );

        $result->execute([
            $user->getId(),
            $user->currentCompanyId(),
            Di::getDefault()->getApp()->getId()
        ]);

        return true;
    }

    /**
     * Get the total notification for the current user.
     *
     * @return int
     */
    public static function totalUnRead(Users $user): int
    {
        return self::count([
            'conditions' => 'is_deleted = 0 AND read = 0 AND users_id = ?0 AND companies_id = ?1 AND apps_id = ?2',
            'bind' => [
                $user->getId(),
                $user->currentCompanyId(),
                Di::getDefault()->getApp()->getId()
            ]
        ]);
    }
}
