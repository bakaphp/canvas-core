<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Cities;

/**
 * Class TimeZonesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class CitiesController extends BaseController
{
    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $countriesId = $this->router->getParams()['countriesId'];
        $statesId = $this->router->getParams()['statesId'];

        if (!is_numeric($statesId)) {
            throw new Exception('The statesId id is invalid');
        }

        if (!is_numeric($countriesId)) {
            throw new Exception('The statesId id is invalid');
        }

        $this->model = new Cities();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['states_id', ':', $statesId],
            ['countries_id', ':', $countriesId]
        ];
    }
}
