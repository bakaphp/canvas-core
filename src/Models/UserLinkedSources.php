<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class UserLinkedSources extends Model
{
    public int $users_id;
    public int $source_id;
    public $source_users_id;
    public string $source_users_id_text;
    public ?string $source_username = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('user_linked_sources');
        $this->belongsTo('users_id', 'Canvas\Models\Users', 'id', ['alias' => 'user']);
        $this->belongsTo('source_id', 'Canvas\Models\Sources', 'id', ['alias' => 'source']);
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

    /**
     * is the user already connected to the social media site?
     *
     * @param  $userData Users
     * @param  $socialNetwork string
     */
    public static function alreadyConnected(Users $userData, $socialNetwork) : bool
    {
        $source = Sources::findFirst(['title = :title:', 'bind' => ['title' => $socialNetwork]]);

        $bind = [
            'source_id' => $source->source_id,
            'users_id' => $userData->users_id,
        ];

        if (self::findFirst(['source_id = :source_id: and users_id = :users_id:', 'bind' => $bind])) {
            return true;
        }

        return false;
    }

    /**
     * Get by source and user Id.
     *
     * @param int $sourceId
     * @param string $socialId
     *
     * @return UserLinkedSources|null
     */
    public static function getBySourceAndSocialId(Sources $source, string $socialId) : ?UserLinkedSources
    {
        return self::findFirst([
            'conditions' => 'source_id = :source_id: and source_users_id_text = :source_users_id_text: and is_deleted = 0',
            'bind' => [
                'source_id' => $source->getId(),
                'source_users_id_text' => $socialId
            ]
        ]);
    }
}
