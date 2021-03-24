<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Baka\Contracts\Auth\UserInterface;
use Baka\Contracts\Notifications\NotificationInterface;
use Baka\Mail\Message;
use Baka\Queue\Queue;
use Canvas\Models\AbstractModel;
use Canvas\Models\Notifications;
use Canvas\Models\NotificationType;
use Canvas\Models\Users;
use Phalcon\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Traits\EventManagerAwareTrait;

class Notification implements NotificationInterface
{
    use EventManagerAwareTrait;

    protected ?UserInterface $toUser = null;
    protected ?UserInterface $fromUser = null;
    protected $type = null;
    protected ?ModelInterface $entity = null;

    /**
     * Send this notification to the queue?
     *
     * @var bool
     */
    protected bool $useQueue = false;

    /**
     * Save the notifications into the db
     *
     * @var bool
     */
    protected bool $saveNotification = true;

    /**
     * Send this notification to pusher
     * 
     * @var bool
     */
    protected bool $toPusher = true;

    /**
     * Send this notification to mail
     * 
     * @var bool
     */
    protected bool $toMail = true;

    /**
     * Send this notification to push notification
     * 
     * @var bool
     */
    protected bool $toPushNotification = true;

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
     * Return the message from the current notification type.
     *
     * @return string
     */
    public function message() : string
    {
        return $this->type->template ?: '';
    }

    /**
     * Define a Baka Mail to send a email.
     *
     * @todo add Interfase to bakaMail
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
     * Set the usre we are sending the notification to.
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
     * Set the usre we are sending the notification to.
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
            $this->setType(NotificationType::getByKey(static::class));
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

        return Queue::send(Queue::NOTIFICATIONS, serialize($notificationData));
    }

    /**
     * Saved the used notification to the database
     *
     * @return boolean
     */
    public function saveNotification(): bool
    {
        $content = $this->message();
        $app = Di::getDefault()->getApp();

        //save to DB
        $notification = new Notifications();
        $notification->from_users_id = $this->fromUser->getId();
        $notification->users_id = $this->toUser->getId();
        $notification->companies_id = $this->fromUser->currentCompanyId();
        $notification->apps_id = $app->getId();
        $notification->system_modules_id = $this->type->system_modules_id;
        $notification->notification_type_id = $this->type->getId();
        $notification->entity_id = $this->entity->getId();
        $notification->content = $content;
        $notification->read = 0;
        $notification->saveOrFail();

        return true;
    }

    /**
     * Send the notification to the places the user defined.
     *
     * @return bool
     */
    public function trigger() : bool
    {
        if($this->saveNotification) {
            $this->saveNotification();
        }

        if($this->toPusher) {
            $this->toPusher();
        }

        if($this->toMail) {
            $this->toMailNotification();
        }

        if($this->toPushNotification) {
            $this->toSendPushNotification();
        }

        return true;
    }

    /**
     * Send to pusher the notification
     *
     * @return boolean
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
     * Send notification to mail
     *
     * @return boolean
     */
    public function toMailNotification() : bool
    {
        $toMail = $this->toMail();
        if ($toMail instanceof Message && !$this->toUser->isUnsubscribe($notification->notification_type_id)) {
            $this->fire('notification:sendMail', $toMail);
        }

        return true;
    }

    /**
     * Send Push notification
     *
     * @return boolean
     */
    public function toSendPushNotification() : bool
    {
        $toPushNotification = $this->toPushNotification();
        if ($toPushNotification instanceof PushNotification) {
            $this->fire('notification:sendPushNotification', $toPushNotification);
        }

        return true;
    }
}
