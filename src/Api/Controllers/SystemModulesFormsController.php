<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\SystemModulesForms;
use Phalcon\Http\Response;

class SystemModulesFormsController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'system_modules_id',
        'name',
        'slug',
        'form_schema'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'system_modules_id',
        'name',
        'slug',
        'form_schema'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new SystemModulesForms();
        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
            ['companies_id', ':', $this->userData->currentCompanyId()],
            ['apps_id', ':', $this->app->getId()],
        ];
    }

    /**
     * Get the record by its slug.
     *
     * @param string $slug
     *
     * @throws Exception
     *
     * @return Response
     */
    public function getBySlug(string $slug) : Response
    {
        return $this->response(SystemModulesForms::findFirstOrFail([
            'conditions' => 'slug = :slug: and apps_id = :apps_id: and companies_id = :companies_id: and is_deleted = 0',
            'bind' => [
                'slug' => $slug,
                'apps_id' => $this->app->getId(),
                'companies_id' => $this->userData->currentCompanyId()
            ]
        ]));
    }
}
