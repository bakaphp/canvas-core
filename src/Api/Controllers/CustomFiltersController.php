<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Contracts\CustomFilters\CustomFilterTrait;
use Baka\Database\CustomFilters\CustomFilters;
use Baka\Http\Exception\BadRequestException;
use Canvas\Dto\CustomFilter as CustomFilterDto;
use Canvas\Mapper\CustomFilterMapper;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;

class CustomFiltersController extends BaseController
{
    use CustomFilterTrait;

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
    protected $updateFields = [
        'name',
        'description',
        'sequence_logic'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFilters();
        $this->model->users_id = $this->userData->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();
        $this->model->companies_branch_id = $this->userData->currentCompanyBranchId();
        $this->model->apps_id = $this->app->getId();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()],
            ['apps_id', ':', $this->app->getId()]
        ];
    }

    /**
     * Process the create request.
     *
     * @return ModelInterface
     *
     * @throws Exception
     */
    protected function processCreate(RequestInterface $request) : ModelInterface
    {
        $this->userData->isAdmin();

        //process the input
        $this->request->validate([
            'criterias' => 'required|array',
        ]);
        $request = $this->processInput($request->getPostData());

        $request['apps_id'] = $this->model->apps_id;
        $request['companies_id'] = $this->model->companies_id;
        $request['companies_branch_id'] = $this->model->companies_branch_id;
        $request['users_id'] = $this->model->users_id;

        $this->model = $this->processFilter($request);
        $this->processCriterias($this->model, $request['criterias']);

        return $this->model;
    }

    /**
     * Process the update request and return the object.
     *
     * @param Request $request
     * @param ModelInterface $record
     *
     * @throws Exception
     *
     * @return ModelInterface
     */
    protected function processEdit(RequestInterface $request, ModelInterface $record) : ModelInterface
    {
        $this->userData->isAdmin();

        $record = parent::processEdit($request, $record);
        $this->request->validate([
            'criterias' => 'required|array',
        ]);
        $request = $this->processInput($request->getPutData());


        $this->updateCriterias($record, $request['criterias']);

        return $record;
    }

    /**
     * Execute a custom filter.
     *
     * @param int $id
     *
     * @return Response
     */
    public function executeCriteria(int $id) : Response
    {
        //find the info
        $record = $this->getRecordById($id);

        if (!class_exists($record->systemModule->model_name)) {
            throw new BadRequestException('Model not found');
        }

        $model = new $record->systemModule->model_name();

        return $this->response(
            $model->findByRawSql($record->getSql())
        );
    }

    /**
     * Format output.
     *
     * @param [type] $results
     *
     * @return void
     */
    protected function processOutput($results)
    {
        //add a mapper
        $this->dtoConfig
            ->registerMapping(CustomFilters::class, CustomFilterDto::class)
            ->useCustomMapper(new CustomFilterMapper());

        return $results instanceof Simple ?
            $this->mapper->mapMultiple(iterator_to_array($results), CustomFilterDto::class)
            : $this->mapper->map($results, CustomFilterDto::class);
    }
}
