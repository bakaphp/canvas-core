<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Cities;
use Canvas\Models\Countries;
use Canvas\Models\States;
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
        $params = $this->router->getParams();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];

        if (key_exists('countriesId', $params)) {
            $this->additionalSearchFields[] = ['countries_id', ':', $params['countriesId']];
        }

        if (key_exists('statesId', $params)) {
            $this->additionalSearchFields[] = ['states_id', ':', $params['statesId']];
        }
    }

    /**
     * getStates.
     *
     * @return Response
     */
    public function getStates() : Response
    {
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
    public function getCities() : Response
    {
        $this->model = new Cities();
        $results = $this->processIndex();

        //return the response + transform it if needed
        return $this->response($results);
    }
}
