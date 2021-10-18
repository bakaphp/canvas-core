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
            Users::class,
            'id',
            ['alias' => 'user', 'reusable' => true]
        );

        $this->belongsTo(
            'from_users_id',
            Users::class,
            'id',
            ['alias' => 'from', 'reusable' => true]
        );

        $this->belongsTo(
            'notification_type_id',
            NotificationType::class,
            'id',
            ['alias' => 'type', 'reusable' => true]
        );
    }

    /**
     * Mark as Read all the notification from a user.
     *
     * @param Users $user
     *
     * @return bool
     */
    public static function markAsRead(Users $user, bool $global = true) : bool
    {
        $statement = 'UPDATE notifications set `read` = 1 WHERE users_id = :users_id  AND apps_id = :apps_id';
        $bind = [
            'users_id' => $user->getId(),
            'apps_id' => Di::getDefault()->get('app')->getId()
        ];

        if (!$global) {
            $statement .= ' AND companies_id = :companies_id';
            $bind['companies_id'] = $user->currentCompanyId();
        }

        $result = Di::getDefault()->get('db')->prepare(
            $statement
        );

        return (bool) $result->execute($bind);
    }

    /**
     * Get the total notification for the current user.
     *
     * @param Users $user
     * @param bool $global
     *
     * @return int
     */
    public static function totalUnRead(Users $user, bool $global = true) : int
    {
        $conditions = 'is_deleted = 0 AND read = 0 AND users_id = :users_id: AND apps_id = :apps_id:';
        $bind = [
            'users_id' => $user->getId(),
            'apps_id' => Di::getDefault()->get('app')->getId()
        ];

        if (!$global) {
            $conditions .= ' AND companies_id = :companies_id:';
            $bind['companies_id'] = $user->currentCompanyId();
        }

        $total = self::count([
            'conditions' => $conditions,
            'bind' => $bind
        ]);

        return  $total > 99 ? 99 : $total;
    }

    /**
     * unsubscribe user for NotificationType.
     *
     * @param Users $user
     * @param int $notificationTypeId
     * @param int $systemModulesId
     *
     * @return NotificationsUnsubscribe
     */
    public static function unsubscribe(Users $user, int $notificationTypeId, int $systemModulesId) : NotificationsUnsubscribe
    {
        return NotificationsUnsubscribe::unsubscribe($user, $notificationTypeId, $systemModulesId);
    }
}
