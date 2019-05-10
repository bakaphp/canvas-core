<?php

namespace Canvas\Notifications\Email;

use Namshi\Notificator\Notification;
use Canvas\Contracts\Notifications\EmailNotificationsInterface as EmailNotificationsContract;
use Canvas\Models\Users as UsersModel;
use Canvas\Models\Notifications;
use Phalcon\Di;
use PhpAmqpLib\Message\AMQPMessage;

class Email extends Notification implements EmailNotificationsContract
{
    public $entity;

    public $content;

    public $systemModule;

    public $user;

    public function __construct(array $entity, string $content, string $systemModule, array $user)
    {
        $this->entity = $entity;
        $this->content  = $content;
        $this->systemModule = $systemModule;
        $this->user = $user;
    }

    /**
     * Assemble Notification
     */
    public function assemble()
    {
        return $this->content;
    }

    /**
     * Create a new Apps Notification
     * @param string $content
     * @param string $systemModule
     * @param UsersModel $user
     * @return void
     */
    public static function apps(string $content, string $systemModule, UsersModel $user = null): void
    {
        if (!isset($user)) {
            $user =  Di::getDefault()->getUserData();
        }
        /**
         * Create an array of  Apps Push Notification
         */
        $notificationArray =  array(
            'entity'=> $user->toArray(),
            'users_id' => Di::getDefault()->getUserData()->getId(),
            'content'=> $content,
            'system_module'=>$systemModule,
            'notification_type_id'=> Notifications::APPS
        );

        /**
         * Convert notification to Rabbitmq message
         */
        $msg =  new AMQPMessage(json_encode($notificationArray, JSON_UNESCAPED_SLASHES), ['delivery_mode' => 2]);

        $channel = Di::getDefault()->getQueue()->channel();

        $channel->basic_publish($msg, '', 'notifications');
    }

    /**
     * Create a new Users Notification
     * @param string $content
     * @param string $systemModule
     * @param Users $user
     * @return void
     */
    public static function users(string $content, string $systemModule, UsersModel $user = null): void
    {
        if (!isset($user)) {
            $user =  Di::getDefault()->getUserData();
        }

        /**
         * Create an array of  Apps Push Notification
         */
        $notificationArray =  array(
            'entity'=> $user->toArray(),
            'users_id' => Di::getDefault()->getUserData()->getId(),
            'content'=> $content,
            'system_module'=>$systemModule,
            'notification_type_id'=> Notifications::USERS
        );


        /**
         * Convert notification to Rabbitmq message
         */
        $msg =  new AMQPMessage(json_encode($notificationArray, JSON_UNESCAPED_SLASHES), ['delivery_mode' => 2]);

        $channel = Di::getDefault()->getQueue()->channel();

        $channel->basic_publish($msg, '', 'notifications');
    }

    /**
     * Create a new System Notification
     * @param string $content
     * @param string $systemModule
     * @param Users $user
     * @return void
     */
    public static function system(string $content, string $systemModule, UsersModel $user = null): void
    {
        if (!isset($user)) {
            $user =  Di::getDefault()->getUserData();
        }

        /**
         * Create an array of  Apps Push Notification
         */
        $notificationArray =  array(
            'entity'=> $user->toArray(),
            'users_id' => Di::getDefault()->getUserData()->getId(),
            'content'=> $content,
            'system_module'=>$systemModule,
            'notification_type_id'=> Notifications::SYSTEM
        );


        /**
         * Convert notification to Rabbitmq message
         */
        $msg =  new AMQPMessage(json_encode($notificationArray, JSON_UNESCAPED_SLASHES), ['delivery_mode' => 2]);

        $channel = Di::getDefault()->getQueue()->channel();

        $channel->basic_publish($msg, '', 'notifications');
    }
}
