<?php

declare(strict_types=1);

namespace Canvas\Notifications;

use Canvas\Contracts\Notifications\NotificationInterfase;
use Canvas\Models\AbstractModel;
use Canvas\Models\NotificationType;
use Baka\Mail\Message;
use Canvas\Models\Users;
use Canvas\Models\Notifications;
use Phalcon\Traits\EventManagerAwareTrait;
use Phalcon\Di;

class Notification implements NotificationInterfase
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

    /**
     * Constructor.
     *
     * @param AbstractModel $entity
     */
    public function __construct(AbstractModel $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Set the notification type.
     *
     * @param NotificationType $type
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
     * @return Message
     */
    protected function toMail(): ?Message
    {
    }

    /**
     * To send push notification.
     *
     * @return void
     */
    protected function toPushNotification()
    {
    }

    /**
     * Send to websocket / realtime.
     *
     * @return void
     */
    protected function toReatime()
    {
        //set the channel
        //key_user_id
    }

    /**
     * Set the usre we are sending the notification to.
     *
     * @param Users $user
     * @return void
     */
    public function setTo(Users $user): void
    {
        $this->toUser = $user;
    }

    /**
     * Set the user from who the notification if comming from.
     *
     * @param User $user
     * @return void
     */
    public function setFrom(Users $user): void
    {
        $this->fromUser = $user;
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
        }

        if (Di::getDefault()->has('mail')) {
            $this->mail = Di::getDefault()->getMail();
        }

        if ($this->useQueue) {
            return true; //send it to the queue
        }

        $this->trigger();

        return true;
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

        if ($this->toMail() instanceof Message) {
            $this->fire('notification:sendMail', $this->toMail());
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
