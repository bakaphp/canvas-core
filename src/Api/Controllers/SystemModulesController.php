<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\SystemModules;
use Phalcon\Http\Response;

/**
 * Class SystemModulesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class SystemModulesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'slug',
        'model_name',
        'browse_fields',
        'show',
        'protected'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'slug',
        'model_name',
        'browse_fields',
        'show',
        'protected'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new SystemModules();
        $this->model->apps_id = $this->app->getId();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
        ];
        $this->sort = 'menu_order|ASC';
    }

    /**
     * Delete a Record.
     *
     * @throws Exception
     *
     * @return Response
     */
    public function delete($id) : Response
    {
        return $this->response('Cant delete System Modules at the moment');
    }
}
