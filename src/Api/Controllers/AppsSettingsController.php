<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Apps;
use Canvas\Dto\AppsSettings;
use Phalcon\Http\Response;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class AppsSettingsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Apps();
    }

    /**
     * Given the model list the records based on the  filter.
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->response([]);
    }

    /**
     * Create new record.
     *
     * @return Response
     */
    public function create(): Response
    {
        return $this->response([]);
    }

    /**
     * Get the record by its primary key.
     *
     * @param mixed $id
     *
     * @throws Exception
     * @return Response
     */
    public function getById($id): Response
    {
        return $this->response([]);
    }

    /**
     * Delete a Record.
     *
     * @throws Exception
     * @return Response
     */
    public function delete($id): Response
    {
        return $this->response([]);
    }

    /**
     * Format output.
     *
     * @param [type] $results
     * @return void
     */
    protected function processOutput($results)
    {
        //DTOAppsSettings
        $this->dtoConfig->registerMapping(Apps::class, AppsSettings::class)
          ->forMember('settings', function (Apps $app) {
              $settings = [];
              foreach ($app->settingsApp->toArray() as $setting) {
                  $settings[$setting['name']] = $setting['value'];
              }
              return $settings;
          });

        return is_iterable($results) ?
                $this->mapper->mapMultiple(iterator_to_array($results), AppsSettings::class)
                : $this->mapper->map($results, AppsSettings::class);
    }

    /**
     * get item.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/data/{id}
     *
     * @return \Phalcon\Http\Response
     */
    public function getByKey($key = null): Response
    {
        //find the info
        $record = $this->model->findFirst([
            'key = ?0 AND is_deleted = 0',
            'bind' => [$key],
        ]);

        //get the results and append its relationships
        $result = $this->appendRelationshipsToResult($this->request, $record);

        return $this->response($this->processOutput($result));
    }
}
