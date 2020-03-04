<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Auth\Models\Sessions;
use Phalcon\Http\Response;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 * @property UserData $userData
 *
 */
class SessionsController extends BaseController
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
        $this->model = new Sessions();
        $this->additionalSearchFields = [
            ['users_id', ':', $this->userData->getId()],
        ];
    }
}
