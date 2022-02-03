<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Users;

use Baka\Contracts\Controllers\ProcessOutputMapperTrait;
use Canvas\Api\Controllers\BaseController;
use Canvas\Dto\User as UserDto;
use Canvas\Mapper\UserMapper;
use Canvas\Models\CompaniesBranches;
use Canvas\Models\Users;
use Phalcon\Http\Response;

class BranchController extends BaseController
{
    use ProcessOutputMapperTrait;

    /**
     * set objects.
     *
     * @return void
     */
    public function onConstruct()
    {
        $this->model = new Users();
        $this->dto = UserDto::class;
        $this->dtoMapper = new UserMapper();
    }

    /**
     * Update a User Info.
     *
     * @param string $roleName
     *
     * @return Response
     */
    public function getUserListByBranchId(int $id) : Response
    {
        $branch = CompaniesBranches::findFirstOrFail([
            'id = ?0 AND is_deleted = 0 and companies_id = ?1',
            'bind' => [
                $id,
                $this->userData->currentCompanyId()
            ],
        ]);

        $this->customConditions = " WHERE id IN (
                SELECT users_id 
                    FROM users_associated_company
                    WHERE 
                        companies_id = {$this->userData->currentCompanyId()}
                        AND companies_branches_id ={$branch->getId()}
                        )";


        return $this->index();
    }
}
