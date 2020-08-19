<?php

namespace Canvas\Cli\Jobs;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Jobs\Job;
use Phalcon\Di;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Client\Common\HttpMethodsClient as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use OneSignal\Config;
use OneSignal\OneSignal;
use Canvas\Models\UserLinkedSources;
use Canvas\Notifications\PushNotification;
use Exception;

class PushNotifications extends Job implements QueueableJobInterface
{
    /**
     * Realtime channel.
     *
     * @var string
     */
    protected $users;

    /**
     * Realtime event.
     *
     * @var string
     */
    protected $message;

    /**
     * Realtime params.
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor setup info for Pusher.
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
     * Handle the pusher request.
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

        if (empty($userDevicesArray[2]) && empty($userDevicesArray[3])) {
            return false;
        }

        /**
         * One signal array params.
         */
        $pushBody = [
            'contents' => [
                'en' => $this->message
            ]
        ];
        
        if(!empty($this->params)){
            $pushBody['data'] = $this->params;
        }

        /**
         * @todo change to use some constante , ID dont tell you what device it is
         */
        //send push android
        if (!empty($userDevicesArray[2])) {
            $pushBody['include_player_ids'][] = $userDevicesArray[2][0];
        }

        //ios
        if (!empty($userDevicesArray[3])) {
            $pushBody['include_player_ids'][] = $userDevicesArray[3][0];
        }

        try {
            $response = $this->oneSignal(
                $config->pushNotifications->appId,
                $config->pushNotifications->authKey,
                $config->pushNotifications->userAuthKey
            )->notifications->add(
                $pushBody
            );
            
            if (Di::getDefault()->has('log')) {
                Di::getDefault()->get('log')->info(
                    'Sending Push Notification to UserId: '. $this->users->getId() .' - ',
                    [$response]
                );
            }
        } catch (Exception $e) {
            if (Di::getDefault()->has('log')) {
                Di::getDefault()->get('log')->error(
                    'Error sending push notification via OneSignal - ' . $e->getMessage(),
                    [$e->getTraceAsString()]
                );
            }
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
    private function oneSignal(string $appId, string $appAuthKey, string $appUserKey): OneSignal
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
