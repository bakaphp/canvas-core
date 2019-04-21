<?php
declare(strict_types=1);

namespace Canvas\Models;

class Notifications extends AbstractModel
{
    /**
     * Apps notication type
     */
    const APPS = 1;

    /**
     * Users notification type
     */
    const USERS = 2;

    /**
     * System notification type
     */
    const SYSTEM = 3;
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
    *
    * @var integer
    */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $system_module_id;

    /**
     *
     * @var integer
     */
    public $notification_type_id;

    /**
     *
     * @var integer
     */
    public $entity_id;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('notifications');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'notifications';
    }
}
