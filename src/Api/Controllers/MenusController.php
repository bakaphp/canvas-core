<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Dto\Menus as MenusDto;
use Canvas\Mapper\MenusMapper;
use Canvas\Models\Menus;
use Phalcon\Http\Response;

class MenusController extends BaseController
{
    use ProcessOutputMapperTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'slug',
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'slug',
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Menus();
        $this->model->apps_id = $this->app->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();
        $this->dto = MenusDto::class;
        $this->dtoMapper = new MenusMapper();

        $this->additionalSearchFields = [
            ['is_deleted', ':', '0'],
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
        //find the info
        $record = Menus::findFirstOrFail([
            'conditions' => 'slug = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind' => [
                $slug,
                $this->app->getId()
            ]
        ]);

        //get the results and append its relationships
        $result = $this->appendRelationshipsToResult($this->request, $record);

        return $this->response($this->processOutput($result));
    }
}
