<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\SystemModules;

/**
 * Class LanguagesController
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
    protected $createFields = [];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [];

    /**
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new SystemModules();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
        ];
    }
}
