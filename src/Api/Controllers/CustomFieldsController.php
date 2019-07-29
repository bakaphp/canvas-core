<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\CustomFields\CustomFields;
use function Canvas\Core\isJson;

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

        if (!empty($request['value']) && is_array($request['value'])) {
            $request['value'] = json_encode($request['value']);
        }

        return $request;
    }

    /**
     * Format output.
     *
     * @param mixed $results
     * @return mixed
     */
    public function processOutput($results)
    {
        //decode json to format output
        if (isset($results->attributes) && !empty($results->attributes) && !is_array($results->attributes)) {
            $results->attributes = json_decode($results->attributes);
        }

        if (isJson($results->value)) {
            $results->value = json_decode($results->value);
        }

        return $results;
    }
}
