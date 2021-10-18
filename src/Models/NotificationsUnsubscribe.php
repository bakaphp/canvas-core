<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class NotificationsUnsubscribe extends AbstractModel
{
    public ?int $users_id = 0;
    public ?int $companies_id = 0;
    public ?int $apps_id = 0;
    public ?int $system_modules_id = 0;
    public ?int $notification_type_id = 0;
    public ?string $email = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notifications_unsubscribe');

        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'notification_type_id',
            NotificationType::class,
            'id',
            ['alias' => 'type']
        );
    }

    /**
     * get NotificationsUnsubscribe by NotificationType.
     *
     * @param Users $user
     * @param int $notificationTypeId
     *
     * @return NotificationsUnsubscribe
     */
    public static function getByNotificationType(Users $user, int $notificationTypeId) : ?NotificationsUnsubscribe
    {
        return NotificationsUnsubscribe::findFirst([
            'conditions' => 'users_id = ?0 AND companies_id = ?1 AND apps_id = ?2 AND \notification_type_id = ?3 AND is_deleted = 0',
            'bind' => [
                0 => $user->getId(),
                1 => $user->currentCompanyId(),
                2 => Di::getDefault()->getApp()->getId(),
                3 => $notificationTypeId
            ]
        ]);
    }

    /**
     * Verify that the user is unsubscribed.
     *
     * @param Users $user
     * @param int $notificationType
     *
     * @return bool
     */
    public static function isUnsubscribe(Users $user, int $notificationTypeId) : bool
    {
        //-1 means it is out of all lists
        $userNotification = NotificationsUnsubscribe::getByNotificationType($user, -1);

        if (!$userNotification) {
            $userNotification = NotificationsUnsubscribe::getByNotificationType($user, $notificationTypeId);
        }

        return $userNotification ? true : false;
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
        $userNotification = NotificationsUnsubscribe::getByNotificationType($user, $notificationTypeId);

        if (!$userNotification) {
            $userNotification = new NotificationsUnsubscribe();
            $userNotification->users_id = $user->getId();
            $userNotification->companies_id = $user->currentCompanyId();
            $userNotification->apps_id = Di::getDefault()->getApp()->getId();
            $userNotification->notification_type_id = $notificationTypeId;
            $userNotification->system_modules_id = $systemModulesId;
            $userNotification->email = $user->getEmail();
        }

        $userNotification->is_deleted = 0;
        $userNotification->saveOrFail();
        return $userNotification;
    }
}
