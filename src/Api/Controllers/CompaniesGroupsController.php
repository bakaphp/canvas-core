<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\CompaniesGroups as CompaniesGroupsDto;
use Canvas\Mapper\CompaniesGroupsMapper;
use Canvas\Models\CompaniesGroups;

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
