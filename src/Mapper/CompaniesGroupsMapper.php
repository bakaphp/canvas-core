<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Models\Subscription;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class CompaniesGroupsMapper extends CustomMapper
{
    /**
     * @param Baka\Database\CustomFilters\CustomFilters $filter
     * @param \Canvas\Dto\CustomFilter $filterSchema
     *
     * @return ListSchema
     */
    public function mapToObject($companiesGroup, $companiesGroupDto, array $context = [])
    {
        $companiesGroupDto->id = $companiesGroup->getId();
        $companiesGroupDto->name = $companiesGroup->name;
        $companiesGroupDto->users_id = $companiesGroup->users_id;
        $companiesGroupDto->apps_id = $companiesGroup->apps_id;
        $companyArray = [];
        /**
         * Let's find all companies and their apps plans.
         */
        foreach ($companiesGroup->getCompanies() as $company) {
            $subscription = Subscription::findFirst([
                'conditions' => 'user_id = ?0 and companies_id = ?1 and is_deleted = 0',
                'bind' => [$companiesGroup->users_id, $company->id]
            ]);

            foreach ($company as $key => $value) {
                $companyArray[$key] = $value;
                $companyArray['app_plan'] = $subscription->getAppPlan();
            }

            $companiesGroupDto->companies[] = $companyArray;
        }
        $companiesGroupDto->created_at = $companiesGroup->created_at;
        $companiesGroupDto->updated_at = $companiesGroup->updated_at;
        $companiesGroupDto->is_deleted = $companiesGroup->is_deleted;

        return $companiesGroupDto;
    }
}
