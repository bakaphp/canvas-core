<?php

declare(strict_types=1);

namespace Canvas;

use Canvas\Models\UserWebhooks;
use Exception;
use Phalcon\Http\Response;
use GuzzleHttp\Client;

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
         * for the SDK
         *  - pass the system module classname or classname\namespace
         *  - pass the action you are doing from the CRUD
         *  - get all the hooks from the user that match this action
         *  - pass the data you are sending 
         *  - then we send it over to the URl
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
}
