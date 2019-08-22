<?php

namespace Canvas\Cli\Jobs;

use Canvas\Contracts\Queue\QueueableJobInterfase;
use Canvas\Jobs\Job;
use Phalcon\Di;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Client\Common\HttpMethodsClient as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use OneSignal\Config;
use OneSignal\OneSignal;
use Canvas\Models\Users;
use Canvas\Models\UserLinkedSources;

class PushNotifications extends Job implements QueueableJobInterfase
{
    /**
     * Realtime channel
     *
     * @var string
     */
    protected $users;

    /**
     * Realtime event
     *
     * @var string
     */
    protected $message;

    /**
     * Realtime params
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor setup info for Pusher
     *
     * @param string $channel
     * @param string $event
     * @param array $params
     */
    public function __construct(Users $users, string $message, array $params)
    {
        $this->users = $users;
        $this->message = $message;
        $this->params = $params;
    }

    /**
     * Handle the pusher request
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->users)) {
            return false;
        }

        $userDevicesArray = [
            2 => [],
            3 => []
        ];

        $linkedSource = UserLinkedSources::find([
            'conditions' => 'users_id = ?0 and source_id in (2,3)',
            'bind' => [$this->users->getId()]
        ]);

        if ($linkedSource) {
            //add to list of devices id
            foreach ($linkedSource as $device) {
                $userDevicesArray[$device->source_id][] = $device->source_users_id_text;
            }
        }

        $pushBodyAndroid = [
            'contents' => [
                'en' => 'Example Android'
            ],
            'data' => ['message' => 'Example Android helllo'],
            'include_player_ids' => $userDevicesArray[2]
        ];

        $pushBodyiOS = [
            'contents' => [
                'en' => 'Example IOS'
            ],
            'data' => ['message' => 'Example IOS'],
            'include_player_ids' => $userDevicesArray[3], //send to a group of users
        ];

        //send push
        if (!empty($userDevicesArray[2])) {
            self::android()->notifications->add($pushBodyAndroid);
        }
        // if (!empty($userDevicesArray[3])) {
        //     self::iOs()->notifications->add($pushBodyiOS);
        // }

        return true;
    }

    /**
     * Give back a one signal object to send push notifications.
     *
     * @param string $appId
     * @param string $appAuthKey
     * @param string $appUserKey
     * @return OneSignal
     */
    private static function oneSignal(string $appId, string $appAuthKey, string $appUserKey): OneSignal
    {
        $config = new Config();
        $config->setApplicationId($appId);
        $config->setApplicationAuthKey($appAuthKey);
        $config->setUserAuthKey($appUserKey);
        
        $guzzle = new GuzzleClient([
            'timeout' => 2.0,
        ]);

        $client = new HttpClient(new GuzzleAdapter($guzzle), new GuzzleMessageFactory());

        return  new OneSignal($config, $client);
    }
    
    /**
     * Return the android one signal object.
     *
     * @return OneSignal
     */
    private static function android(): OneSignal
    {
        return self::oneSignal(getenv('CANVAS_ANDROID_APP_ID'), getenv('CANVAS_ANDROID_AUTH_KEY'), getenv('CANVAS_ANDROID_APP_USER_AUTH_KEY'));
    }

    /**
     * Return the iOs one signal object.
     *
     * @return OneSignal
     */
    private static function iOs(): OneSignal
    {
        return self::oneSignal(getenv('CANVAS_IOS_APP_ID'), getenv('CANVAS_IOS_AUTH_KEY'), getenv('CANVAS_IOS_APP_USER_AUTH_KEY'));
    }
}
