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
}
