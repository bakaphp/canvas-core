<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Auth\Models\UserLinkedSources as BakaUserLinkedSources;

class UserLinkedSources extends BakaUserLinkedSources
{
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
     * Get all user linked sources by user's id.
     *
     * @param int $usersId
     *
     * @return array
     */
    public static function getMobileUserLinkedSources(int $usersId) : array
    {
        $userDevicesArray = [
            2 => [],
            3 => []
        ];

        /**
         * @todo change this from ID's to use the actual definition of the android / ios apps
         */
        $linkedSource = UserLinkedSources::find([
            'conditions' => 'users_id = ?0 and source_id in (2,3) AND is_deleted = 0',
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
