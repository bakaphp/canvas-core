<?php

declare(strict_types=1);

namespace Canvas;

use Canvas\Models\UserWebhooks;
use Throwable;
use Phalcon\Http\Response;
use GuzzleHttp\Client;
use Phalcon\Di;
use Canvas\Models\SystemModules;
use function Canvas\Core\isJson;

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
    public static function run(int $id, array $data)
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

        try {
            /**
             * @todo move the guzzle reqeust to Async for faster performance
             */
            $clientRequest = $client->request(
                $userWebhook->method,
                $userWebhook->url,
                self::formatData($userWebhook->method, $data)
            );

            $responseContent = $clientRequest->getBody()->getContents();
            $response = isJson($responseContent) ? json_decode($responseContent, true) : $responseContent;
        } catch (Throwable $error) {
            $response = $error->getMessage();
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
    public static function process(string $module, array $data, string $action): array
    {
        $appId = Di::getDefault()->getApp()->getId();
        $company = Di::getDefault()->getUserData()->getDefaultCompany();

        $systemModule = SystemModules::getByName($module);

        $webhooks = UserWebhooks::find([
            'conditions' => 'apps_id = ?0 AND companies_id = ?1 
                            AND webhooks_id in 
                                (SELECT Canvas\Models\Webhooks.id FROM Canvas\Models\Webhooks 
                                    WHERE Canvas\Models\Webhooks.apps_id = ?0 
                                    AND Canvas\Models\Webhooks.system_modules_id = ?2 
                                    AND Canvas\Models\Webhooks.action = ?3
                                )',
            'bind' => [
                $appId,
                $company->getId(),
                $systemModule->getId(),
                $action
            ]
        ]);

        $results = [];

        if ($webhooks->count()) {
            foreach ($webhooks as $userWebhook) {
                $results[$userWebhook->webhook->name][$action][] = [
                    $userWebhook->url => [
                        'results' => self::run($userWebhook->getId(), $data),
                    ]
                ];
            }

            return $results;
        }

        return [
            'message' => 'No user configure webhooks found for : ',
            'module' => $module,
            'data' => $data,
            'action' => $action
        ];
    }

    /**
     * Format the data for guzzle correct usage
     *
     * @return array
     */
    public static function formatData(string $method, array $data): array
    {
        switch (ucwords($method)) {
            case 'GET':
                $updateDataFormat = [
                    'query' => $data
                ];
                break;

            case 'PUT':
            case 'POST':
                    $updateDataFormat = [
                        'json' => $data,
                        'form_params' => $data
                    ];
                break;
            default:
                $updateDataFormat = [];
                break;
        }

        return $updateDataFormat;
    }
}
