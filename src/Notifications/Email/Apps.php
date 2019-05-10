<?php

namespace Canvas\Notifications\Email;

use Namshi\Notificator\Notification;
use Canvas\Contracts\Notifications\EmailNotificationsInterface as EmailNotificationsContract;
use Canvas\Models\Notifications;
use Canvas\Models\EmailTemplates;
use Canvas\Notifications\Mobile\Mobile;
use Canvas\Traits\NotificationsTrait;
use Phalcon\Di;

class Apps extends Email implements EmailNotificationsContract
{
    /**
     * Notifications Trait
     */
    use NotificationsTrait;

    /**
     * Assemble an Apps Push Notification
     * @todo Create specific assembler for apps push notifications
     */
    public function assemble()
    {
        /**
         * Create a new database record
         */
        self::create((array)$this->entity,(array)$this->user, $this->content, Notifications::APPS, $this->systemModule);

        /**
         * Search for specific email template for System
         */
        $template = EmailTemplates::findFirst([
            'conditions'=>'companies_id in (0,?0) and apps_id in (0,?1) and name = ?2 and users_id in (1,?3) and is_deleted = 0',
            'bind'=>[$this->user['default_company'],Di::getDefault()->getConfig()->app->id,'email-apps',$this->user['id']]
        ]);



        if(!is_object($template)){
            Di::getDefault()->getLog()->error(current($template->getMessages()));
        }

        //Fetch and return specific template for Apps Email Notifications
        return $template;
    }
}
