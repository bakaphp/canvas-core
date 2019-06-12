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
class AppsController extends BaseController
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
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['id', ':', implode('|', $this->userData->getAssociatedApps())],
        ];
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
              return $app->settingsApp->toArray();
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
    public function getById($id = null): Response
    {
        //find the info
        $record = $this->model->findFirst([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$id],
        ]);

        //get the results and append its relationships
        $result = $this->appendRelationshipsToResult($this->request, $record);

        return $this->response($this->processOutput($result));
    }
}
