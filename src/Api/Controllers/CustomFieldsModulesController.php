<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Exception\UnauthorizedException;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\CustomFields\CustomFields;
use Canvas\Dto\CustomFieldsModules as CustomFieldsModulesDto;
use Canvas\Mapper\CustomFieldsModulesMapper;
use Canvas\Models\CustomFieldsModules;
use Phalcon\Http\Response;

class CustomFieldsModulesController extends BaseController
{
    use ProcessOutputMapperTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'model_name'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'model_name'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFieldsModules();
        $this->dto = CustomFieldsModulesDto::class;
        $this->dtoMapper = new CustomFieldsModulesMapper();
        $this->model->apps_id = $this->app->getId();

        if (!$this->userData->hasRole('Defaults.Admins')) {
            throw new UnauthorizedException('You dont have permission to run this action ');
        }
    }

    /**
     * Fetch all Custom Fields of a Module.
     *
     * @param int $id
     *
     * @return Response
     */
    public function customFieldsByModulesId(int $id) : Response
    {
        //Verify that module exists
        $module = $this->model::findFirstOrFail([
            'conditions' => 'id = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind' => [$id, $this->app->getId()]
        ]);

        //List all Custom Fields by module_id, apps and companies
        $customFields = CustomFields::findOrFail([
            'conditions' => 'companies_id = ?0 and custom_fields_modules_id = ?1 and apps_id = ?2 and is_deleted = 0',
            'bind' => [$this->userData->currentCompanyId(), $module->id, $this->app->getId()]
        ]);

        return $this->response($customFields);
    }
}
