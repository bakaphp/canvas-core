<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\States;

/**
 * Class TimeZonesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class StatesController extends BaseController
{
    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new States();
        $countriesId = $this->router->getParams()['countriesId'];

        if (!is_numeric($countriesId)) {
            throw new Exception('The countriesId id is invalid');
        }

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['countries_id', ':', $countriesId]
        ];
    }
}
