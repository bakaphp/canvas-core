<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\CustomFields\CustomFields;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Http\Request;
use Canvas\Dto\CustomFields as CustomFieldsDto;
use Canvas\Mapper\CustomFieldsMapper;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 * @property Users $userData
 * @property Apps $app
 *
 */
class CustomFieldsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'label', 'custom_fields_modules_id', 'fields_type_id', 'attributes'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'label', 'custom_fields_modules_id', 'fields_type_id', 'attributes'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFields();
        $this->model->users_id = $this->userData->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();
        $this->model->apps_id = $this->app->getId();

        $this->additionalSearchFields = [
            ['apps_id', ':', $this->app->getId()],
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }

    /**
     * Process the input data.
     *
     * @param array $request
     * @return array
     */
    protected function processInput(array $request): array
    {
        //encode the attribute field from #teamfrontend
        if (!empty($request['attributes']) && is_array($request['attributes'])) {
            $request['attributes'] = json_encode($request['attributes']);
        }

        return $request;
    }

    /**
     * Pass the resultset to a DTO Mapper.
     *
     * @param mixed $results
     * @return void
     */
    protected function processOutput($results)
    {
        $this->dtoConfig->registerMapping(CustomFields::class, CustomFieldsDto::class)
            ->useCustomMapper(new CustomFieldsMapper());

        return is_iterable($results) ?
        $this->mapper->mapMultiple($results, CustomFieldsDto::class)
        : $this->mapper->map($results, CustomFieldsDto::class);
    }

    /**
    * Process the create request and trecurd the boject.
    *
    * @return ModelInterface
    * @throws Exception
    */
    protected function processCreate(Request $request): ModelInterface
    {
        $model = parent::processCreate($request);
        $request = $request->getPostData();

        //add values to the custom field
        if (is_array($request['values'])) {
            $model->addValues($request['values']);
        }

        return $model;
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
        //process the input
        $record = parent::processEdit($request, $record);
        $request = $request->getPostData();

        //add values to the custom field
        if (is_array($request['values'])) {
            $record->addValues($request['values']);
        }
        return $record;
    }
}
