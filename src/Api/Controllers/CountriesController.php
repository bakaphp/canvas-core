<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Locations\Cities;
use Canvas\Models\Locations\Countries;
use Canvas\Models\Locations\States;
use Phalcon\Http\Response;

/**
 * Class TimeZonesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class CountriesController extends BaseController
{
    /*
         * fields we accept to create
         *
         * @var array
         */
    protected $createFields = [
        'name',
        'flag',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'flag',
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Countries();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }

    /**
     * getStates.
     *
     * @return Response
     */
    public function getStates(int $countriesId) : Response
    {
        $this->additionalSearchFields[] = ['countries_id', ':', $countriesId];

        $this->model = new States();
        $results = $this->processIndex();

        //return the response + transform it if needed
        return $this->response($results);
    }

    /**
     * getCities.
     *
     * @return Response
     */
    public function getCities(int $countriesId, int $statesId) : Response
    {
        $this->additionalSearchFields[] = ['countries_id', ':', $countriesId];
        $this->additionalSearchFields[] = ['states_id', ':', $statesId];

        $this->model = new Cities();
        $results = $this->processIndex();

        //return the response + transform it if needed
        return $this->response($results);
    }
}
