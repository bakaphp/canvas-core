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
use Canvas\Notifications\PushNotification;

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
    public function __construct(PushNotification $pushNotification)
    {
        $this->users = $pushNotification->to;
        $this->message = $pushNotification->message;
        $this->params = $pushNotification->params;
    }

    /**
     * Handle the pusher request
     *
     * @return void
     */
    public function handle()
    {
        $config = Di::getDefault()->getConfig();

        if (empty($this->users)) {
            return false;
        }

        $userDevicesArray = UserLinkedSources::getMobileUserLinkedSources($this->users->getId());

        $pushBodyAndroid = [
            'contents' => [
                'en' => $this->message
            ],
            'data' => ['message' => ''],
            'include_player_ids' => $userDevicesArray[2]
        ];

        $pushBodyiOS = [
            'contents' => [
                'en' => $this->message
            ],
            'data' => ['message' => ''],
            'include_player_ids' => $userDevicesArray[3]
        ];

        //send push
        if (!empty($userDevicesArray[2])) {
            self::oneSignal(
                $config->pushNotifications->appId,
                $config->pushNotifications->authKey,
                $config->pushNotifications->userAuthKey
            )->notifications->add(
                $pushBodyAndroid
            );
        }

        if (!empty($userDevicesArray[3])) {
            self::oneSignal(
                $config->pushNotifications->appId,
                $config->pushNotifications->authKey,
                $config->pushNotifications->userAuthKey
            )->notifications->add(
                $pushBodyiOS
            );
        }

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
}
