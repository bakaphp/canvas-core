<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class Notifications extends AbstractModel
{
    public ?int $from_users_id = 0;
    public ?int $users_id = 0;
    public ?int $companies_id = 0;
    public ?int $apps_id = 0;
    public ?int $system_modules_id = 0;
    public ?int $notification_type_id = 0;
    public int $entity_id = 0;
    public ?string $content = null;
    public int $read = 0;

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
     * Mark as Read all the notification from a user.
     *
     * @param Users $user
     *
     * @return void
     */
    public static function markAsRead(Users $user) : bool
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
    public static function totalUnRead(Users $user) : int
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

    /**
     * Verify that the user is unsubscribed
     * @return bool
     */
    public function isUnsubscribe(?Users $user) : bool
    {
        $user = is_null($user) ? $this->user : $user;
        return NotificationsUnsubscribe::isUnsubscribe($user, $this->notification_type_id);
    }

    /**
    * unsubscribe user for NotificationType
    * @param Users $user
    * @param int $notificationTypeId
    * @param int $systemModulesId
    * @return NotificationsUnsubscribe
    */
    public static function unsubscribe(Users $user, int $notificationTypeId, int $systemModulesId) : NotificationsUnsubscribe
    {
        return NotificationsUnsubscribe::unsubscribe($user, $notificationTypeId, $systemModulesId);
    }
}
