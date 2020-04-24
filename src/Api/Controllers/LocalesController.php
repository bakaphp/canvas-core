<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Locales;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class LocalesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Locales();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
