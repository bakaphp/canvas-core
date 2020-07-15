<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Menus as MenusDto;
use Canvas\Mapper\MenusMapper;
use Canvas\Models\Menus;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 *
 */
class MenusController extends BaseController
{
    use ProcessOutputMapperTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'slug',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'slug',
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Menus();
        $this->model->apps_id = $this->app->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();
        $this->dto = MenusDto::class;
        $this->dtoMapper = new MenusMapper();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['apps_id', ':', $this->app->getId()],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }
}
