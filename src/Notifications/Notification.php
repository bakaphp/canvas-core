<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Notifications\NotificationInterface;
use function Baka\isJson;
use Baka\Mail\Message;
use Baka\Queue\Queue;
use Canvas\Contracts\EventManagerAwareTrait;
use Canvas\Models\AbstractModel;
use Canvas\Models\Notifications;
use Canvas\Models\Notifications\UserEntityImportance;
use Canvas\Models\Notifications\UserSettings;
use Canvas\Models\NotificationType;
use Canvas\Models\Users;
use Canvas\Notifications\Users as NotificationsUsers;
use Phalcon\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use Throwable;

class Notification implements NotificationInterface
{
    use EventManagerAwareTrait;

    protected ?UserInterface $toUser = null;
    protected ?UserInterface $fromUser = null;
    protected $type = null;
    protected ?ModelInterface $entity = null;
    protected string $message = '';
    protected ?Notifications $currentNotification = null;
    protected bool $enableGroupable = false;
    protected bool $overWriteMessage = false;

    /**
     * Send this notification to the queue?
     *
     * @var bool
     */
    protected bool $useQueue = false;

    /**
     * Save the notifications into the db.
     *
     * @var bool
     */
    protected bool $saveNotification = true;

    /**
     * Send this notification to pusher.
     *
     * @var bool
     */
    protected bool $toPusher = true;

    /**
     * Send this notification to mail.
     *
     * @var bool
     */
    protected bool $toMail = true;

    /**
     * Send this notification to push notification.
     *
     * @var bool
     */
    protected bool $toPushNotification = true;

    /**
     * Allows notifications to be groupable or not groupable.
     *
     * @var bool
     */
    protected bool $groupable = false;

    /**
     * The minimum time in minutes to consider before grouping notifications.
     *
     * @var int
     */
    protected int $softCap = 0;

    /**
     * The maximum time in minutes to consider before grouping notifications.
     *
     * @var int
     */
    protected int $hardCap = 10;

    /**
     * Group by entity.
     *
     * @var bool
     */
    protected bool $groupByEntity = false;

    /**
     *
     * @var Baka\Mail\Manager
     */
    protected $mail;

    const USERS = NotificationsUsers::class;
    const SYSTEM = System::class;
    const APPS = Apps::class;

    /**
     * Constructor.
     *
     * @param AbstractModel $entity
     */
    public function __construct(Model $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Set the notification type.
     *
     * @param NotificationType $type
     *
     * @return void
     */
    public function setType(NotificationType $type) : void
    {
        $this->type = $type;
    }

    /**
     * Set the soft cap in minutes.
     *
     * @param int $softCap
     *
     * @return void
     */
    public function setSoftCap(int $softCap) : void
    {
        $this->softCap = $softCap;
    }

    /**
     * Set the hard cap in minutes.
     *
     * @param int $hardCap
     *
     * @return void
     */
    public function setHardCap(int $hardCap) : void
    {
        $this->hardCap = $hardCap;
    }

    /**
     * Set group by entity.
     *
     * @param bool $hardCap
     *
     * @return void
     */
    public function setGroupByEntity(bool $groupByEntity) : void
    {
        $this->groupByEntity = $groupByEntity;
    }

    /**
     * Allow use to overwrite the user message for group notifications.
     *
     * @param bool $overWriteMessage
     *
     * @return void
     */
    public function setOverWriteMessage(bool $overWriteMessage) : void
    {
        $this->overWriteMessage = $overWriteMessage;
    }

    /**
     * Set groupable flag.
     *
     * @param bool $hardCap
     *
     * @return void
     */
    public function setGroupable(bool $groupable) : void
    {
        $this->enableGroupable = $groupable;
    }

    /**
     * Return the message from the current notification type.
     *
     * @return string
     */
    public function message() : string
    {
        return $this->type->template ?: $this->message;
    }

    /**
     * setMessage.
     *
     * @param  string $message
     *
     * @return void
     */
    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }

    /**
     * Define a Baka Mail to send a email.
     *
     * @todo add Interface to bakaMail
     *
     * @return Message
     */
    protected function toMail() : ?Message
    {
        return null;
    }

    /**
     * To send push notification.
     *
     * @return void
     */
    protected function toPushNotification() : ?PushNotification
    {
        return null;
    }

    /**
     * Send to websocket / realtime.
     *
     * @return void
     */
    protected function toRealtime() : ?PusherNotification
    {
        return null;
    }

    /**
     * Set the user we are sending the notification to.
     *
     * @param Users $user
     *
     * @return void
     */
    public function setTo(UserInterface $user) : void
    {
        $this->toUser = $user;
    }

    /**
     * Set the user from who the notification if coming from.
     *
     * @param User $user
     *
     * @return void
     */
    public function setFrom(UserInterface $user) : void
    {
        $this->fromUser = $user;
    }

    /**
     * Set the user we are sending the notification to.
     *
     * @return Users
     */
    public function getTo() : ?UserInterface
    {
        return $this->toUser;
    }

    /**
     * get the user from who the notification if coming from.
     *
     * @return User
     */
    public function getFrom() : ?UserInterface
    {
        return $this->fromUser;
    }

    /**
     * Disable this notification queue in runtime.
     *
     * @return void
     */
    public function disableQueue() : void
    {
        $this->useQueue = false;
    }

    /**
     * Disable saving the notification.
     *
     * @return void
     */
    public function disableSaveNotification() : void
    {
        $this->saveNotification = false;
    }

    /**
     * Disable send to pusher notification.
     *
     * @return void
     */
    public function disableToPusher() : void
    {
        $this->toPusher = false;
    }

    /**
     * Disable send to mail.
     *
     * @return void
     */
    public function disableToMail() : void
    {
        $this->toMail = false;
    }

    /**
     * Disable send Push notification.
     *
     * @return void
     */
    public function disablePushNotification() : void
    {
        $this->toPushNotification = false;
    }

    /**
     * Has group Message.
     *
     * @return bool
     */
    public function hasGroupMessage() : bool
    {
        return !empty($this->currentNotification->content_group);
    }

    /**
     * Process the notification
     *  - handle the db
     *  - trigger the notification
     *  - knows if we have to send it to queue.
     *
     * @return bool
     */
    public function process() : bool
    {
        //if the user didn't provide the type get it based on the class name
        try {
            if (is_null($this->type)) {
                $this->setType(
                    NotificationType::getByKeyOrCreate(
                        static::class,
                        $this->entity
                    )
                );
            } elseif (is_string($this->type)) {
                //not great but for now lets use it
                $this->setType(NotificationType::getByKey($this->type));
            }

            if (Di::getDefault()->has('mail')) {
                $this->mail = Di::getDefault()->get('mail');
            }

            if ($this->useQueue) {
                $this->sendToQueue();
                return true; //send it to the queue
            }

            $this->trigger();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Send to our internal Notification queue.
     *
     * @return bool
     */
    public function sendToQueue() : bool
    {
        $notificationData = [
            'from' => $this->fromUser,
            'to' => $this->toUser,
            'entity' => $this->entity,
            'type' => $this->type,
            'notification' => get_class($this),
        ];

        return Queue::send(
            Queue::NOTIFICATIONS,
            serialize($notificationData)
        );
    }

    /**
     * Send the notification to the places the user defined.
     *
     * @return bool
     */
    public function trigger() : bool
    {
        if ($this->saveNotification) {
            $this->saveNotification();
        }

        if ($this->sendNotificationEnabled()) {
            if ($this->toPusher) {
                $this->toPusher();
            }

            if ($this->toMail) {
                $this->toMailNotification();
            }

            if ($this->toPushNotification) {
                $this->toSendPushNotification();
            }
        }

        return true;
    }

    /**
     * Check the current user setting to know if he wants to receive
     * the current type of notification.
     *
     * @return bool
     */
    protected function sendNotificationEnabled() : bool
    {
        $sendNotificationByImportance = true;
        $app = Di::getDefault()->get('app');

        //is this type of notification enabled for this user?
        $sendNotification = UserSettings::isEnabled(
            $app,
            $this->toUser,
            $this->type
        );

        //does he want to receive this type of notification from the current entity?
        if ($this->fromUser instanceof UserInterface) {
            $toUserSettlings = UserEntityImportance::getByEntity(
                $app,
                $this->toUser,
                $this->fromUser
            );

            if ($toUserSettlings
                    && is_object($toUserSettlings->importance)
                    && $this->currentNotification instanceof Notifications
                ) {
                $sendNotificationByImportance = $toUserSettlings->importance->validateExpression($this->currentNotification);
            }

            return $sendNotification && $sendNotificationByImportance;
        }
    }

    /**
     * Send to pusher the notification.
     *
     * @return bool
     */
    public function toPusher() : bool
    {
        $toRealtime = $this->toRealtime();
        if ($toRealtime instanceof PusherNotification) {
            $this->fire('notification:sendRealtime', $toRealtime);
        }

        return true;
    }

    /**
     * Send notification to mail.
     *
     * @return bool
     */
    public function toMailNotification() : bool
    {
        $toMail = $this->toMail();
        if ($toMail instanceof Message && !$this->toUser->isUnsubscribe($this->type->getId())) {
            $this->fire('notification:sendMail', $toMail);
        }

        return true;
    }

    /**
     * Send Push notification.
     *
     * @return bool
     */
    public function toSendPushNotification() : bool
    {
        $toPushNotification = $this->toPushNotification();
        if ($toPushNotification instanceof PushNotification) {
            $this->fire('notification:sendPushNotification', $toPushNotification);
        }

        return true;
    }

    /**
     * Save the notification used to the database.
     *
     * @return bool
     */
    public function saveNotification() : bool
    {
        $app = Di::getDefault()->get('app');
        $isGroupable = $this->enableGroupable ? $this->isGroupable() : null;

        //save to DB
        if (is_null($isGroupable)) {
            $this->currentNotification = new Notifications();
            $this->currentNotification->from_users_id = $this->fromUser->getId();
            $this->currentNotification->users_id = $this->toUser->getId();
            $this->currentNotification->companies_id = $this->fromUser->currentCompanyId();
            try {
                $this->currentNotification->companies_branches_id = $this->fromUser->currentBranchId();
            } catch (Throwable $e) {
                $this->currentNotification->companies_branches_id = 0;
            }
            $this->currentNotification->apps_id = $app->getId();
            $this->currentNotification->system_modules_id = $this->type->system_modules_id;
            $this->currentNotification->notification_type_id = $this->type->getId();
            $this->currentNotification->entity_id = $this->entity->getId();
            $this->currentNotification->content = $this->message();
            $this->currentNotification->read = 0;
        } else {
            $this->currentNotification = Notifications::findFirstById($isGroupable);

            if (!$this->groupByEntity) {
                $this->groupNotification();
            } else {
                $this->groupNotificationEntity();
            }

            if ($this->overWriteMessage) {
                $this->currentNotification->content = $this->message();
            }
        }

        $this->currentNotification->saveOrFail();

        return true;
    }

    /**
     * Groups a set of notifications.
     *
     * @return void
     */
    protected function groupNotification() : void
    {
        $notificationGroup = $this->currentNotification->content_group;

        $currentUser = [
            'id' => $this->fromUser->getId(),
            'name' => $this->fromUser->displayname,
            'displayname' => $this->fromUser->displayname,
            'photo' => $this->fromUser->getPhoto()
        ];

        if (empty($notificationGroup)) {
            $mainUser = Users::findFirst($this->currentNotification->from_users_id);

            //if its from the same user we ignore
            if ($mainUser->getId() === $this->fromUser->getId()) {
                return;
            }

            $notificationGroup = [
                'from_users' => [
                    [
                        'id' => $mainUser->getId(),
                        'name' => $mainUser->displayname,
                        'displayname' => $mainUser->displayname,
                        'photo' => $mainUser->getPhoto()
                    ],
                    $currentUser
                ]
            ];
        } else {
            if (!isJson($notificationGroup)) {
                return;
            }

            $notificationGroup = json_decode($notificationGroup);

            if (!$this->canAddNewUser($notificationGroup->from_users)) {
                return;
            }

            $notificationGroup->from_users[] = $currentUser;
        }

        $this->currentNotification->content_group = json_encode($notificationGroup);
        $this->groupContent();
    }

    /**
     * Group by Entity.
     *
     * @return void
     */
    protected function groupNotificationEntity() : void
    {
        $notificationGroup = $this->currentNotification->content_group;

        if (!isJson($this->currentNotification->content_group)) {
            $notificationGroup = [
                'total' => 2,
            ];
        } else {
            $notificationGroup = json_decode($notificationGroup, true);
            $notificationGroup['total']++;
        }
        $this->currentNotification->content_group = json_encode($notificationGroup);
    }

    /**
     * Verifies if the user is already on that group notification
     * and validates that the length is not grater than 10.
     *
     * @param  array $notificationGroup
     *
     * @return bool
     */
    protected function canAddNewUser(array $groupUsers) : bool
    {
        $isInGroup = true;

        if (count($groupUsers) >= 10) {
            return false;
        }

        foreach ($groupUsers as $user) {
            if ($user->id == $this->fromUser->id) {
                $isInGroup = false;
                break;
            }
        }

        return $isInGroup;
    }

    /**
     * Modifies the notification content adding the amount of users in that notification group.
     *
     * @return void
     */
    protected function groupContent() : void
    {
        if (is_null($this->currentNotification->content_group)
            || !isJson($this->currentNotification->content_group)
            ) {
            return;
        }

        $group = json_decode($this->currentNotification->content_group);
        $usersCount = count($group->from_users) - 1;

        if ($usersCount > 0) {
            //$group->from_users[0]->name , we don't need to add the username
            $newMessage = 'and other ' . $usersCount . ' users ' . $this->message();
            $this->currentNotification->content = $newMessage;
        }
    }

    /**
     * Identify if notification's should be a group.
     *
     * @return bool
     */
    protected function isGroupable() : ?int
    {
        $notificationId = null;

        $query = "SELECT * FROM notifications
                    WHERE notification_type_id = {$this->type->getId()}";

        if ($this->groupByEntity) {
            $query .= " AND entity_id = {$this->entity->getId()}";
        }

        $query .= " AND users_id = {$this->toUser->getId()}
        AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) BETWEEN {$this->softCap} AND {$this->hardCap}
        ORDER BY updated_at DESC limit 1";

        $notification = Notifications::findByRawSql($query);

        if (!empty($notification->toArray())) {
            $notificationId = (int) $notification[0]->getId();
        }

        return $notificationId;
    }
}
