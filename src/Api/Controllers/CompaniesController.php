<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Companies;
use Phalcon\Http\Response;
use Baka\Http\Contracts\Api\CrudCustomFieldsBehaviorTrait;
use Canvas\Http\Exception\UnauthorizedException;

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
    protected $createFields = [
        'name',
        'profile_image',
        'website',
        'users_id',
        'address',
        'zipcode',
        'email',
        'language',
        'timezone',
        'currency_id',
        'phone'
    ];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = [
        'name',
        'profile_image',
        'website',
        'address',
        'zipcode',
        'email',
        'language',
        'timezone',
        'currency_id',
        'phone'];

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
            throw new UnauthorizedException(_('You dont have permission to update this company info'));
        }

        //process the input
        $result = $this->processEdit($this->request, $company);

        return $this->response($this->processOutput($result));
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
            throw new UnauthorizedException(_('You dont have permission to delete this company'));
        }

        $company->is_deleted = 1;
        $company->updateOrFail();

        return $this->response(['Delete Successfully']);
    }

    /**
     * Format output.
     *
     * @param mixed $results
     * @return mixed
     */
    protected function processOutput($results)
    {
        /**
         * Check if the branches exists on results.
         */
        if (array_key_exists('branch', $results)) {
            /**
            * Format branches as an array of branches even if there is only one branch per company.
            */
            foreach ($results as $key => $value) {
                /*   if (is_object($value['branch'])) {
                      $results[$key]['branch'] = [$value['branch']];
                  } */
            }

            /**
             * Format branches as an array of branches even if there is only one branch in a unique company.
             */
            if (is_object(current($results)['branch'])) {
                $results[0]['branch'] = [current($results)['branch']];
            }
        }

        return $results;
    }
}
