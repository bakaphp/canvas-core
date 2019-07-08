<?php
declare(strict_types=1);

namespace Canvas\Models;

class Notifications extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $from_users_id;
    
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
    public $system_modules_id;

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
    public $read;

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

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'from_users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'from']
        );


        $this->belongsTo(
            'notification_type_id',
            'Canvas\Models\NotificationType',
            'id',
            ['alias' => 'type']
        );
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
