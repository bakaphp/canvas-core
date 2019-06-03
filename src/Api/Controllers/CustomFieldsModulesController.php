<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\CustomFieldsModules;
use Canvas\CustomFields\CustomFields;
use Phalcon\Http\Response;
use Canvas\Exception\NotFoundHttpException;
use Canvas\Exception\PermissionException;

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
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'model_name'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'model_name'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CustomFieldsModules();
        $this->model->apps_id = $this->app->getId();

        if (!$this->userData->hasRole('Defaults.Admins')) {
            throw new PermissionException('You dont have permission to run this action ');
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
            throw new NotFoundHttpException('Module not found');
        }

        //List all Custom Fields by module_id, apps and companies
        $customFields = CustomFields::find([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind' => [$this->userData->currentCompanyId(), $this->app->getId()]
        ]);

        if (!is_object($customFields)) {
            throw new NotFoundHttpException('Custom Fields not found');
        }

        return $this->response($customFields);
    }
}
