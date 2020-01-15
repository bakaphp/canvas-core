<?php

declare(strict_types=1);

namespace Canvas;

use Canvas\Models\UserWebhooks;
use Exception;
use Phalcon\Http\Response;
use GuzzleHttp\Client;
use Phalcon\Di;
use Canvas\Models\SystemModules;

/**
 * Class Validation.
 *
 * @package Canvas
 */
class Webhooks
{
    /**
    * Given the weebhook id, we run a test for it.
    *
    * @param integer $id
    * @param mixed $data
    * @return Response
    */
    public static function run(int $id, $data)
    {
        /**
         * 1- verify it s acorrect url
         * 2- verify the method
         * 3- get the entity info from one entity of this app and company
         * 4- guzzle the request with the info
         * 5- verify you got a 200
         * 6- return the response from the webhook.
         *
         * later - add job for all system module to execute a queue when CRUD acction are run, maybe the middleware would do this?
         *
         */
        $userWebhook = UserWebhooks::getById($id);

        $client = new Client();
        $parse = function ($error) {
            if ($error->hasResponse()) {
                return $error->getResponse();
            }
            return json_decode($error->getMessage());
        };

        try {
            $clientRequest = $client->request(
                $userWebhook->method,
                $userWebhook->url,
                $data
            );

            $response = $clientRequest->getBody();
        } catch (Exception $error) {
            $response = $parse($error);
        }

        return $response;
    }

    /**
     * Execute the the webhook for the given app company providing the system module
     * for the SDK
     *  - pass the system module classname or classname\namespace
     *  - pass the action you are doing from the CRUD
     *  - get all the hooks from the user that match this action
     *  - pass the data you are sending
     *  - then we send it over to the URl.
     *
     * @param string $model
     * @param mixed $data
     * @param string $action
     * @throws Exception
     * @return bool
     */
    public static function process(string $module, $data, string $action): bool
    {
        $appId = Di::getDefault()->getApp()->getId();
        $company = Di::getDefault()->getUserData()->getDefaultCompany();

        $systemModule = SystemModules::getByModelName($module);

        $webhooks = UserWebhooks::find([
            'conditions' => 'apps_id = ?0 AND companies_id = ?1 
                            AND webhooks_id in 
                                (SELECT id FROM Canvas\Models\Webhooks WHERE apps_id = ?0 AND system_modules_id = ?2 AND action = ?3)',
            'bind' => [
                $appId,
                $company->getId(),
                $systemModule->getId(),
                $action
            ]
        ]);

        if ($webhook->count()) {
            foreach ($webhooks as $webhook) {
                self::run($webhook->getId(), $data);
            }

            return true;
        }

        return false;
    }
}
