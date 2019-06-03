<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Database\CustomFilters\CustomFilters;
use Canvas\Dto\CustomFilter as CustomFilterDto;
use Baka\Database\Contracts\CustomFilters\CustomFilterTrait;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Http\Request;
use RuntimeException;
use Canvas\Mapper\CustomFilterMapper;

/**
 * Class BaseController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property \Baka\Mail\Message $mail
 * @property Apps $app
 */
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
    protected $updateFields = [];

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
     * Process the create request and trecurd the boject.
     *
     * @return ModelInterface
     * @throws Exception
     */
    protected function processCreate(Request $request): ModelInterface
    {
        //process the input
        $request = $this->processInput($request->getPostData());

        $request['apps_id'] = $this->model->apps_id;
        $request['companies_id'] = $this->model->companies_id;
        $request['companies_branch_id'] = $this->model->companies_branch_id;
        $request['users_id'] = $this->model->users_id;

        $this->model = $this->processFilter($request);
        $this->processsCriterias($this->model, $request['criterias']);

        return $this->model;
    }

    /**
     * Process the update request and return the object.
     *
     * @param Request $request
     * @param ModelInterface $record
     * @throws Exception
     * @return ModelInterface
     */
    protected function processEdit(Request $request, ModelInterface $record): ModelInterface
    {
        $record = parent::processEdit($request, $record);
        $request = $this->processInput($request->getPutData());
        if (!array_key_exists('criterias', $request)) {
            throw new RuntimeException('Expected Criteria key on this array');
        }

        $this->updateCriterias($record, $request['criterias']);

        return $record;
    }

     /**
     * Format output.
     *
     * @param [type] $results
     * @return void
     */
    protected function processOutput($results)
    {
        
         //add a mapper
        $this->dtoConfig->registerMapping(CustomFilters::class, CustomFilterDto::class)
            ->useCustomMapper(new CustomFilterMapper());

        return $results instanceof \Phalcon\Mvc\Model\Resultset\Simple ?
            $this->mapper->mapMultiple(iterator_to_array($results), CustomFilterDto::class)
            : $this->mapper->map($results, CustomFilterDto::class);
    }
}
