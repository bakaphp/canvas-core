<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\UserConfig;

class UserConfigController extends BaseController
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
        $this->model = new UserConfig();
        $this->additionalSearchFields = [
            ['users_id', ':', $this->userData->getId()],
        ];
    }
}
