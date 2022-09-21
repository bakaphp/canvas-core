<?php

namespace Canvas\Cli\Jobs;

use Baka\Contracts\Queue\QueueableJobInterface;
use Baka\Jobs\Job;
use Canvas\Enums\Sources;
use Canvas\Models\Notifications;
use Canvas\Models\UserLinkedSources;
use Canvas\Models\Users;
use Canvas\Notifications\PushNotification;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Client\Common\HttpMethodsClient as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use OneSignal\Config;
use OneSignal\OneSignal;
use Phalcon\Di;

class PushNotifications extends Job implements QueueableJobInterface
{
    protected Users $users;
    protected string $message;
    protected string $title;
    protected const IOS = 3;
    protected const ANDROID = 2;
    /**
     * Realtime params.
     *
     * @var array
     */
    protected ?array $params;

    /**
     * Push notification construct.
     *
     * @param PushNotification $pushNotification
     */
    public function __construct(PushNotification $pushNotification)
    {
        $this->users = $pushNotification->to;
        $this->message = $pushNotification->message;
        $this->params = $pushNotification->params;
        $this->title = $pushNotification->title;
    }

    /**
     * Handle the pusher request.
     *
     * @return bool
     */
    public function handle()
    {
        $config = Di::getDefault()->get('config');

        $userDevicesArray = UserLinkedSources::getMobileUserLinkedSources($this->users->getId());

        if (empty($userDevicesArray[Sources::IOS]) && empty($userDevicesArray[Sources::ANDROID]) && empty($userDevicesArray[Sources::WEBAPP])) {
            return false;
        }

        /**
         * One signal array params.
         */
        $pushBody = [
            'contents' => [
                'en' => $this->message
            ],
            'headings' => [
                'en' => $this->title
            ]
        ];

        if (!empty($this->params)) {
            $pushBody['data'] = $this->params;
        }

        //if IOS add badge
        if (!empty($userDevicesArray[Sources::IOS]) 
            && (!(bool) $this->user->get('disable_ios_badge_count')) {
            $pushBody['ios_badgeType'] = 'SetTo';
            $pushBody['ios_badgeCount'] = Notifications::totalUnRead($this->users);
        }

        /**
         * @todo We need to use external_users_id instead of player_id in the future
         * for proper multi-device notification.
         */
        foreach ($userDevicesArray as $userDevicesSourcesArray) {
            if (!empty($userDevicesSourcesArray)) {
                foreach ($userDevicesSourcesArray as $userDevice) {
                    $pushBody['include_player_ids'][] = $userDevice;
                }
            }
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
                    'OneSignal Sending Push Notification to UserId: ' . $this->users->getId() . ' - ',
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
     *
     * @return OneSignal
     */
    private function oneSignal(string $appId, string $appAuthKey, string $appUserKey) : OneSignal
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
