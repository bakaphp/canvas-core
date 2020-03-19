<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CustomFieldsModules;
use Canvas\CustomFields\CustomFields;
use Phalcon\Http\Response;
use Canvas\Http\Exception\NotFoundException;
use Canvas\Http\Exception\UnauthorizedException;
use Canvas\Dto\CustomFieldsModules as CustomFieldsModulesDto;
use Canvas\Mapper\CustomFieldsModulesMapper;
use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;

/**
 * Class LanguagesController.
 *
 * @package Canvas\Api\Controllers
 * @property Users $userData
 * @property Apps $app
 *
 */
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
     * @param integer $id
     * @return Response
     */
    public function customFieldsByModulesId(int $id): Response
    {
        //Verify that module exists
        $module = $this->model::findFirst([
            'conditions' => 'id = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind' => [$id, $this->app->getId()]
        ]);

        if (!is_object($module)) {
            throw new NotFoundException('Module not found');
        }

        //List all Custom Fields by module_id, apps and companies
        $customFields = CustomFields::find([
            'conditions' => 'companies_id = ?0 and custom_fields_modules_id = ?1 and apps_id = ?2 and is_deleted = 0',
            'bind' => [$this->userData->currentCompanyId(), $module->id, $this->app->getId()]
        ]);

        if (!is_object($customFields)) {
            throw new NotFoundException('Custom Fields not found');
        }

        return $this->response($customFields);
    }
}
