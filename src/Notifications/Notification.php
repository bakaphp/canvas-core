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
use Phalcon\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;

class Notification implements NotificationInterface
{
    use EventManagerAwareTrait;

    protected ?UserInterface $toUser = null;
    protected ?UserInterface $fromUser = null;
    protected $type = null;
    protected ?ModelInterface $entity = null;
    protected string $message = '';
    protected ?Notifications $currentNotification = null;

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
     * The minimum time to consider before grouping notifications.
     *
     * @var int
     */
    protected int $softCap = 0;

    /**
     * The maximun time to consider before grouping notifications.
     *
     * @var int
     */
    protected int $hardCap = 5;


    /**
     *
     * @var Baka\Mail\Manager
     */
    protected $mail;

    const USERS = 'Canvas\Notifications\Users';
    const SYSTEM = 'Canvas\Notifications\System';
    const APPS = 'Canvas\Notifications\Apps';

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
     * Set the soft cap.
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
     * Set the hard cap.
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
     * Set groupable flag.
     *
     * @param bool $hardCap
     *
     * @return void
     */
    public function setGroupable(bool $groupable) : void
    {
        $this->groupable = $groupable;
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
     * Save the notification used to the database.
     *
     * @return bool
     */
    public function saveNotification() : bool
    {
        $content = $this->message();
        $app = Di::getDefault()->get('app');
        $isGroupable = ($this->groupable) ? $this->isGroupable() : null;

        //save to DB
        if (is_null($isGroupable)) {
            $this->currentNotification = new Notifications();
            $this->currentNotification->from_users_id = $this->fromUser->getId();
            $this->currentNotification->users_id = $this->toUser->getId();
            $this->currentNotification->companies_id = $this->fromUser->currentCompanyId();
            $this->currentNotification->apps_id = $app->getId();
            $this->currentNotification->system_modules_id = $this->type->system_modules_id;
            $this->currentNotification->notification_type_id = $this->type->getId();
            $this->currentNotification->entity_id = $this->entity->getId();
            $this->currentNotification->content = $content;
            $this->currentNotification->read = 0;
        } else {
            $this->currentNotification = Notifications::findFirstById($isGroupable);
            $this->groupNotification();
        }

        $this->currentNotification->saveOrFail();

        return true;
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

        //those he want to receive this type of notification from the current entity?
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
     * Groups a set of notifications.
     *
     * @return void
     */
    protected function groupNotification() : void
    {
        $notificationGroup = $this->currentNotification->group;

        $currentUser = [
            'id' => $this->fromUser->getId(),
            'name' => $this->fromUser->displayname,
            'photo' => $this->fromUser->getPhoto()
        ];

        if (empty($notificationGroup)) {
            $mainUser = Users::findFirstById($this->currentNotification->from_users_id);

            $notificationGroup = [
                'from_users' => [[
                    'id' => $mainUser->getId(),
                    'name' => $mainUser->displayname,
                    'photo' => $mainUser->getPhoto()
                ], $currentUser]
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

        $this->currentNotification->group = json_encode($notificationGroup);
        $this->groupContent();
    }

    /**
     * Verifies if the user is already on that grup notification and validates that the lenght is not grater than 10.
     *
     * @param  array $notificationGroup
     * @return bool
     */
    protected function canAddNewUser(array $groupUsers) : bool
    {
        $isInGroup = true;

        if (count($groupUsers) > 10) {
            return false;
        }

        foreach ($groupUsers as $user) {
            if ($user->email == $this->fromUser->email) {
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
        if (is_null($this->currentNotification->group) || !isJson($this->currentNotification->group)) {
            return;
        }

        $group = json_decode($this->currentNotification->group);
        $usersCount = count($group);

        if ($usersCount > 0) {
            $newMessage = $group->from_users[0]->name . ' and other ' . $usersCount . ' users ' . $this->message();
            $this->currentNotification->content = $newMessage;
        }
    }

    /**
     * Identify if notifcationes should be a group.
     *
     * @return bool
     */
    protected function isGroupable() : ?int
    {
        $notificationId = null;

        $sql = "SELECT * FROM notifications
                    WHERE notification_type_id = {$this->type->getId()}
                    AND entity_id = {$this->entity->getId()}
                    AND TIMESTAMPDIFF(MINUTE, updated_at, NOW()) between {$this->softCap} and {$this->hardCap}
                    order by updated_at DESC limit 1";

        $notification = Notifications::findByRawSql($sql);

        if (!empty($notification->toArray())) {
            $notificationId = (int) $notification[0]->getId();
        }

        return $notificationId;
    }
}
