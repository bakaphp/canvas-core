<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Baka\Http\Exception\UnprocessableEntityException;
use Canvas\Models\CompaniesBranches;
use Phalcon\Http\Response;

/**
 * Class CompaniesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 */
class CompaniesBranchesController extends BaseController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = [
        'name',
        'address',
        'email',
        'zipcode',
        'phone',
        'is_default'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'address',
        'email',
        'zipcode',
        'phone',
        'is_default'
    ];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new CompaniesBranches();
        $this->model->users_id = $this->userData->getId();
        $this->model->companies_id = $this->userData->currentCompanyId();

        $this->additionalSearchFields = [
            ['companies_id', ':', $this->userData->currentCompanyId()],
        ];
    }

    /**
     * Get Uer.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/company/{id}
     *
     * @return Response
     */
    public function getById($id) : Response
    {
        //find the info
        $record = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0 and companies_id = ?1',
            'bind' => [$id, $this->userData->currentCompanyId()],
        ]);

        //get the results and append its relationships
        $result = $this->appendRelationshipsToResult($this->request, $record);

        return $this->response($this->processOutput($result));
    }

    /**
     * Update a User Info.
     *
     * @method PUT
     * @url /v1/company/{id}
     *
     * @return Response
     */
    public function edit($id) : Response
    {
        $company = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0 and companies_id = ?1',
            'bind' => [$id, $this->userData->currentCompanyId()],
        ]);

        $request = $this->request->getPutData();

        //update
        if ($company->updateOrFail($request, $this->updateFields)) {
            return $this->response($this->processOutput($company));
        } else {
            //didnt work
            throw new UnprocessableEntityException((string) current($company->getMessages()));
        }
    }
}
