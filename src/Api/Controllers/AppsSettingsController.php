<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Api\BaseController as BakaBaseController;
use Canvas\Dto\AppsSettings;
use Canvas\Models\Apps;
use Phalcon\Http\Response;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class AppsSettingsController extends BakaBaseController
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
     * Format output.
     *
     * @param mixed $results
     *
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
    public function getByKey($key = null) : Response
    {
        //find the info
        $record = $this->model->findFirstOrFail([
            'key = ?0 AND is_deleted = 0',
            'bind' => [$key],
        ]);

        return $this->response($this->processOutput($record));
    }
}
