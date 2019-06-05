<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Companies;
use Phalcon\Http\Response;
use Canvas\Exception\UnauthorizedHttpException;
use Canvas\Exception\UnprocessableEntityHttpException;
use Baka\Http\Contracts\Api\CrudCustomFieldsBehaviorTrait;
use Canvas\Dto\CompaniesBranches;

/**
 * Class CompaniesController.
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 */
class CompaniesController extends BaseController
{
    use CrudCustomFieldsBehaviorTrait;

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'profile_image', 'website', 'users_id', 'address', 'zipcode', 'email', 'language', 'timezone', 'currency_id', 'phone'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'profile_image', 'website', 'address', 'zipcode', 'email', 'language', 'timezone', 'currency_id', 'phone'];

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Companies();

        $this->model->users_id = $this->userData->getId();

        //my list of avaiable companies
        $this->additionalSearchFields = [
            ['id', ':', implode('|', $this->userData->getAssociatedCompanies())],
        ];
    }

    /**
     * List items.
     *
     * @method GET
     * url /v1/controller
     *
     * @param mixed $id
     * @return \Phalcon\Http\Response
     */
    public function index(): Response
    {
        $results = $this->processIndex();
        return $this->response($this->processOutput($results));
    }

    /**
     * Update an item.
     *
     * @method PUT
     * url /v1/companies/{id}
     *
     * @param mixed $id
     *
     * @return \Phalcon\Http\Response
     * @throws \Exception
     */
    public function edit($id): Response
    {
        $company = $this->model->findFirstOrFail($id);
        if (!$company->userAssociatedToCompany($this->userData) && !$this->userData->hasRole('Default.Admins')) {
            throw new UnauthorizedHttpException(_('You dont have permission to update this company info'));
        }

        $data = $this->request->getPutData();

        if (empty($data)) {
            throw new UnprocessableEntityHttpException('No valid data sent.');
        }

        //set the custom fields to update
        $company->setCustomFields($data);

        //update
        if ($company->update($data, $this->updateFields)) {
            return $this->response($this->processOutput($company));
        } else {
            //didnt work
            throw new UnprocessableEntityHttpException($company->getMessages()[0]);
        }
    }

    /**
     * Delete an item.
     *
     * @method DELETE
     * url /v1/companies/{id}
     *
     * @param mixed $id
     *
     * @return \Phalcon\Http\Response
     * @throws \Exception
     */
    public function delete($id): Response
    {
        $company = $this->model->findFirstOrFail($id);
        if (!$company->userAssociatedToCompany($this->userData) && !$this->userData->hasRole('Default.Admins')) {
            throw new UnauthorizedHttpException(_('You dont have permission to delete this company'));
        }

        if ($company->delete() === false) {
            foreach ($company->getMessages() as $message) {
                throw new UnprocessableEntityHttpException($message);
            }
        }

        return $this->response(['Delete Successfully']);
    }

    /**
     * Format output.
     *
     * @param [type] $results
     * @return void
     */
    protected function processOutput($results)
    {
        foreach ($results as $key => $value) {
            if (is_object($value['branch'])) {
                $results[$key]['branch'] = array($value['branch']);
            }
        }

        if (is_object(current($results)['branch'])) {
            $results[0]['branch'] = array(current($results)['branch']);
        }

        return $results;
    }
}
