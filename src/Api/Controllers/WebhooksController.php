<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Webhooks;
use Phalcon\Http\Response;

/**
 * Class LanguagesController
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 *
 */
class WebhooksController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'system_modules_id'
        ,'name'
        ,'description'
        ,'action'
        ,'format'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'system_modules_id'
        ,'name'
        ,'description'
        ,'action'
        ,'format'
    ];

    /**
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Webhooks();
        $this->model->apps_id = $this->app->getId();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Given the weebhook id, we run a test for it
     *
     * @param integer $id
     * @return Response
     */
    public function test(int $id): Response
    {

        /**
         * 1- verify it s acorrect url
         * 2- verify the method
         * 3- get the entity info from one entity of this app and company
         * 4- guzzle the request with the info
         * 5- verify you got a 200
         * 6- return the response from the webhook
         * 
         * later - add job for all system module to execute a queue when CRUD acction are run, maybe the middleware would do this?
         */

        return $this->response([

        ]);
    }
}
