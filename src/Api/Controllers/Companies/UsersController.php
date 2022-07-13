<?php

declare(strict_types=1);

namespace Canvas\Api\Controllers\Companies;

use Baka\Http\Exception\UnprocessableEntityException;
use Canvas\Api\Controllers\BaseController;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesBranches;
use Canvas\Models\UserRoles;
use Canvas\Models\Users;
use Canvas\Models\UsersAssociatedApps;
use Canvas\Models\UsersAssociatedCompanies;
use Exception;
use Phalcon\Http\Response;

class UsersController extends BaseController
{
    /**
     * Remove user from a company.
     *
     * @param int $id
     *
     * @return Response
     */
    public function remove(int $companyId, int $userId) : Response
    {
        if (!$this->userData->isAdmin()) {
            throw new UnprocessableEntityException('You are not allowed to do this action');
        }

        $user = Users::findFirstOrFail($userId);
        $company = Companies::findFirstOrFail($companyId);

        ///cant remove if i hve just 1 company
        $userAssociatedCompanies = UsersAssociatedCompanies::find([
            'conditions' => 'users_id = :users_id:',
            'bind' => [
                'users_id' => $user->getId(),
            ],
        ]);

        if ($this->userData->getId() === $user->getId() && $userAssociatedCompanies->count() === 1) {
            throw new UnprocessableEntityException('You cant remove yourself from the company');
        }

        $userAssociatedCompanies = UsersAssociatedCompanies::findFirstOrFail([
            'conditions' => 'users_id = :users_id: AND companies_id = :companies_id:',
            'bind' => [
                'users_id' => $user->getId(),
                'companies_id' => $company->getId(),
            ],
        ]);

        $userAssociatedApps = UsersAssociatedApps::findFirstOrFail([
            'conditions' => 'users_id = :users_id: AND companies_id = :companies_id:',
            'bind' => [
                'users_id' => $user->getId(),
                'companies_id' => $company->getId(),
            ],
        ]);

        $userRoles = UserRoles::findFirstOrFail([
            'conditions' => 'users_id = :users_id: AND companies_id = :companies_id:',
            'bind' => [
                'users_id' => $user->getId(),
                'companies_id' => $company->getId(),
            ],
        ]);

        $userAssociatedCompanies->delete();
        $userAssociatedApps->delete();
        $userRoles->delete();

        $userAssociatedCompanies = UsersAssociatedCompanies::find([
            'conditions' => 'users_id = :users_id:',
            'bind' => [
                'users_id' => $user->getId(),
            ],
        ]);

        //overwrite the default company id
        if ($userAssociatedCompanies->count()) {
            $user->set(
                Companies::cacheKey(),
                $userAssociatedCompanies->getFirst()->companies_id
            );
        }

        return $this->response('User Removed from the Company');
    }

    /**
     * Remove user from a company.
     *
     * @param int $id
     *
     * @return Response
     */
    public function removeFromBranch(int $branchId, int $userId) : Response
    {
        if (!$this->userData->isAdmin()) {
            throw new UnprocessableEntityException('You are not allowed to do this action');
        }

        $user = Users::findFirstOrFail($userId);
        $branch = CompaniesBranches::findFirstOrFail($branchId);
        $company = $branch->company;

        ///cant remove if i hve just 1 company
        $userAssociatedCompanies = UsersAssociatedCompanies::find([
            'conditions' => 'users_id = :users_id:',
            'bind' => [
                'users_id' => $user->getId(),
            ],
        ]);

        if ($this->userData->getId() === $user->getId() && $userAssociatedCompanies->count() === 1) {
            throw new UnprocessableEntityException('You cant remove yourself from the company');
        }

        //DefaultCompanyBranchApp_2_104
        try {
            $userAssociatedCompanies = UsersAssociatedCompanies::findFirstOrFail([
                'conditions' => 'users_id = :users_id: 
                                AND companies_id = :companies_id:
                                AND companies_branches_id = :companies_branches_id:',
                'bind' => [
                    'users_id' => $user->getId(),
                    'companies_branches_id' => $branch->getId(),
                    'companies_id' => $company->getId(),
                ],
            ]);

            $userAssociatedCompanies->delete();

            $userAssociatedCompanies = UsersAssociatedCompanies::find([
                'conditions' => 'users_id = :users_id:',
                'bind' => [
                    'users_id' => $user->getId(),
                ],
            ]);

            //overwrite the default company id
            if ($userAssociatedCompanies->count()) {
                $user->set(
                    $company->branchCacheKey(),
                    $userAssociatedCompanies->getFirst()->companies_branches_id
                );
            }
        } catch (Exception $e) {
        }

        return $this->response('User Removed from the Company');
    }
}
