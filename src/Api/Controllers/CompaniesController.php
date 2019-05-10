<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers;

use Canvas\Models\Companies;
use Canvas\Models\CompaniesCustomFields;
use Phalcon\Http\Response;
use Canvas\Exception\UnauthorizedHttpException;
use Canvas\Exception\UnprocessableEntityHttpException;
use Baka\Http\QueryParserCustomFields;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleRecords;

/**
 * Class CompaniesController
 *
 * @package Canvas\Api\Controllers
 *
 * @property Users $userData
 * @property Request $request
 */
class CompaniesController extends BaseCustomFieldsController
{
    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $createFields = ['name', 'profile_image', 'website', 'users_id', 'address', 'zipcode', 'email', 'language', 'timezone', 'currency_id','phone'];

    /*
     * fields we accept to create
     *
     * @var array
     */
    protected $updateFields = ['name', 'profile_image', 'website', 'address', 'zipcode', 'email', 'language', 'timezone', 'currency_id','phone'];

    /**
     * set objects
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Companies();
        $this->customModel = new CompaniesCustomFields();

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
    public function index($id = null): Response
    {
        if ($id != null) {
            return $this->getById($id);
        }

        //parse the rquest
        $parse = new QueryParserCustomFields($this->request->getQuery(), $this->model);
        $parse->appendParams($this->additionalSearchFields);
        $parse->appendCustomParams($this->additionalCustomSearchFields);
        $parse->appendRelationParams($this->additionalRelationSearchFields);
        $params = $parse->request();

        $results = (new SimpleRecords(null, $this->model, $this->model->getReadConnection()->query($params['sql'], $params['bind'])));
        $count = $this->model->getReadConnection()->query($params['countSql'], $params['bind'])->fetch(\PDO::FETCH_OBJ)->total;
        $relationships = false;

        // Relationships, but we have to change it to sparo full implementation
        if ($this->request->hasQuery('relationships')) {
            $relationships = $this->request->getQuery('relationships', 'string');
        }

        //navigate los records
        $newResult = [];
        foreach ($results as $key => $record) {
            //field the object
            foreach ($record->getAllCustomFields() as $key => $value) {
                $record->{$key} = $value;
            }

            $newResult[] = !$relationships ? $record->toFullArray() : QueryParserCustomFields::parseRelationShips($relationships, $record);
        }

        unset($results);

        /**
         * @todo Find a way to accomplish this same logic with Mapper later.
         */
        if (is_object(current($newResult)['branch'])) {
            $newResult[0]['branch'] = array(current($newResult)['branch']);
        }

        //this means the want the response in a vuejs format
        if ($this->request->hasQuery('format')) {
            $limit = (int)$this->request->getQuery('limit', 'int', 25);

            $newResult = [
                'data' => $newResult,
                'limit' => $limit,
                'page' => $this->request->getQuery('page', 'int', 1),
                'total_pages' => ceil($count / $limit)
            ];
        }

        return $this->response($newResult);
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
        if ($company = $this->model->findFirst($id)) {
            if (!$company->userAssociatedToCompany($this->userData) && !$this->userData->hasRole('Default.Admins')) {
                throw new UnauthorizedHttpException(_('You dont have permission to update this company info'));
            }

            $data = $this->request->getPut();

            if (empty($data)) {
                throw new UnprocessableEntityHttpException('No valid data sent.');
            }

            //set the custom fields to update
            $company->setCustomFields($data);

            //update
            if ($company->update($data, $this->updateFields)) {
                return $this->getById($id);
            } else {
                //didnt work
                throw new UnprocessableEntityHttpException($company->getMessages()[0]);
            }
        } else {
            throw new UnprocessableEntityHttpException(_('Company doesnt exist'));
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
        if ($company = $this->model->findFirst($id)) {
            if (!$company->userAssociatedToCompany($this->userData) && !$this->userData->hasRole('Default.Admins')) {
                throw new UnauthorizedHttpException(_('You dont have permission to delete this company'));
            }

            if ($company->delete() === false) {
                foreach ($company->getMessages() as $message) {
                    throw new UnprocessableEntityHttpException($message);
                }
            }

            return $this->response(['Delete Successfully']);
        } else {
            throw new UnprocessableEntityHttpException(_('Company doesnt exist'));
        }
    }
}
