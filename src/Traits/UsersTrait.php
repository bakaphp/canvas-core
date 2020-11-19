<?php

declare(strict_types=1);

namespace Canvas\Traits;

use Canvas\Auth\Models\Companies;
use Canvas\Auth\Models\Users;
use Exception;
use Phalcon\Http\Response;

/**
 * Trait FractalTrait
 *
 * @package Canvas\Traits
 */
trait UsersTrait
{
    /**
     * Get Uer.
     *
     * @param mixed $id
     *
     * @method GET
     * @url /v1/users/{id}
     *
     * @return Phalcon\Http\Response
     */
    public function getById($id) : Response
    {
        //find the info
        $user = $this->model->findFirstOrFail([
            'id = ?0 AND is_deleted = 0',
            'bind' => [$this->userData->getId()],
        ]);

        //get the results and append its relationships
        $user = $this->appendRelationshipsToResult($this->request, $user);

        return $this->response($this->processOutput($user));
    }

    /**
     * Update a User Info.
     *
     * @method PUT
     * @url /v1/users/{id}
     *
     * @return Phalcon\Http\Response
     */
    public function edit($id) : Response
    {
        $user = $this->model->findFirstOrFail($this->userData->getId());

        $request = $this->request->getPut();

        if (empty($request)) {
            $request = $this->request->getJsonRawBody(true);
        }

        //clean pass
        if (array_key_exists('password', $request) && !empty($request['password'])) {
            $user->password = Users::passwordHash($request['password']);
            unset($request['password']);
        }

        //clean default company
        if (array_key_exists('default_company', $request)) {
            //@todo check if I belong to this company
            if ($company = Companies::findFirst($request['default_company'])) {
                $user->default_company = $company->getId();
                unset($request['default_company']);
            }
        }

        //update
        $user->updateOrFail($request, $this->updateFields);
        return $this->response($this->processOutput($user));
    }

    /**
     * Given the results we will proess the output
     * we will check if a DTO transformer exist and if so we will send it over to change it.
     *
     * @param object|array $results
     *
     * @return void
     */
    protected function processOutput($results)
    {
        //remove the user password
        if (is_object($results)) {
            $results->password = null;
        }
        return $results;
    }

    /**
     * Add a new user.
     *
     * @method POST
     * @url /v1/users
     * @overwrite
     *
     * @return Phalcon\Http\Response
     */
    public function create() : Response
    {
        throw new Exception('Route not found');
        return $this->response('Route not found');
    }
}
