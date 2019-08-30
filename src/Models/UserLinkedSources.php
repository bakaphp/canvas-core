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

    /**
     * Get all user linked sources by user's id
     * @param int $usersId
     * @return array
     */
    public static function getMobileUserLinkedSources(int $usersId): array
    {

        $userDevicesArray = [
            2 => [],
            3 => []
        ];

        $linkedSource = UserLinkedSources::find([
            'conditions' => 'users_id = ?0 and source_id in (2,3)',
            'bind' => [$usersId]
        ]);

        if ($linkedSource) {
            //add to list of devices id
            foreach ($linkedSource as $device) {
                $userDevicesArray[$device->source_id][] = $device->source_users_id_text;
            }
        }

        return $userDevicesArray;

    }
}
