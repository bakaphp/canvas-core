<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CompaniesGroups;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\CompaniesGroups as CompaniesGroupsDto;
use Canvas\Mapper\CompaniesGroupsMapper;

/**
 * Class CompaniesGroupsController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 */
class CompaniesGroupsController extends BaseController
{
    use ProcessOutputMapperTrait;
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
        $this->model = new CompaniesGroups();
        $this->dto = CompaniesGroupsDto::class;
        $this->dtoMapper = new CompaniesGroupsMapper();
    }
}
