<?php

declare (strict_types = 1);

namespace Canvas\Notifications;

use Baka\Mail\Message;
use Canvas\Contracts\Auth\UserInterface;
use Canvas\Contracts\Notifications\NotificationInterface;
use Canvas\Models\AbstractModel;
use Canvas\Models\Notifications;
use Canvas\Models\NotificationType;
use Canvas\Models\Users;
use Canvas\Queue\Queue;
use Phalcon\Di;
use Phalcon\Mvc\Model;
use Phalcon\Traits\EventManagerAwareTrait;

class Notification implements NotificationInterface
{
    use EventManagerAwareTrait;

    /**
     *
     * @var Users
     */
    protected $toUser = null;

    /**
     *
     * @var Users
     */
    protected $fromUser = null;

    /**
     * Send this notification to the queue?
     *
     * @var boolean
     */
    protected $useQueue = false;

    /**
     *
     * @var NotificationType
     */
    protected $type = null;

    /**
     *
     * @var AbstractModel
     */
    protected $entity = null;

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
    public function setType(NotificationType $type): void
    {
        $this->type = $type;
    }

    /**
     * Return the message from the current notification type.
     *
     * @return string
     */
    public function message(): string
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
    protected function toMail(): ?Message
    {
        return null;
    }

    /**
     * To send push notification.
     *
     * @return void
     */
    protected function toPushNotification(): ?PushNotification
    {
        return null;
    }

    /**
     * Send to websocket / realtime.
     *
     * @return void
     */
    protected function toRealtime(): ?PusherNotification
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
    public function setTo(UserInterface $user): void
    {
        $this->toUser = $user;
    }

    /**
     * Set the user from who the notification if comming from.
     *
     * @param User $user
     *
     * @return void
     */
    public function setFrom(UserInterface $user): void
    {
        $this->fromUser = $user;
    }

    /**
     * Set the usre we are sending the notification to.
     *
     * @return Users
     */
    public function getTo(): ?UserInterface
    {
        return $this->toUser;
    }

    /**
     * get the user from who the notification if comming from.
     *
     * @return User
     */
    public function getFrom(): ?UserInterface
    {
        return $this->fromUser;
    }

    /**
     * Disable this notification queue in runtime.
     *
     * @return void
     */
    public function disableQueue(): void
    {
        $this->useQueue = false;
    }

    /**
     * Process the notification
     *  - handle the db
     *  - trigger the notification
     *  - knows if we have to send it to queu.
     *
     * @return boolean
     */
    public function process(): bool
    {
        //if the user didnt provide the type get it based on the class name
        if (is_null($this->type)) {
            $this->setType(NotificationType::getByKey(static::class));
        } elseif (is_string($this->type)) {
            //not great but for now lets use it
            $this->setType(NotificationType::getByKey($this->type));
        }

        if (Di::getDefault()->has('mail')) {
            $this->mail = Di::getDefault()->getMail();
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
     * @return boolean
     */
    protected function sendToQueue(): bool
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
     * Send the noficiatino to the places the user defined.
     *
     * @return boolean
     */
    public function trigger(): bool
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

        $toMail = $this->toMail();
        if ($toMail instanceof Message) {
            $this->fire('notification:sendMail', $toMail);
        }

        $toPushNotification = $this->toPushNotification();
        if ($toPushNotification instanceof PushNotification) {
            $this->fire('notification:sendPushNotification', $toPushNotification);
        }

        $toRealtime = $this->toRealtime();
        if ($toRealtime instanceof PusherNotification) {
            $this->fire('notification:sendRealtimeNotification', $toRealtime);
        }

        /**
         * @todo send to push ontification
         */

        if ($this->type->with_realtime) {
            $this->fire('notification:sendRealtime', $this);
        }

        return true;
    }
}
