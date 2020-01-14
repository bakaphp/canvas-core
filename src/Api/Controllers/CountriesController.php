<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Countries;

/**
 * Class TimeZonesController
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
}
