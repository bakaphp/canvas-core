<?php
declare(strict_types=1);

namespace Canvas\Models;

class UserLinkedSources extends \Baka\Auth\Models\UserLinkedSources
{
    /**
     *
     * @var integer
     */
    public $source_id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var string
     */
    public $source_users_id;

    /**
     *
     * @var string
     */
    public $source_users_id_text;

    /**
     *
     * @var string
     */
    public $source_username;

    /**
     *
     * @var integer
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

        $this->setSource('user_linked_sources');
        $this->belongsTo('users_id', 'Canvas\Models\Users', 'id', ['alias' => 'user']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'user_linked_sources';
    }
}
