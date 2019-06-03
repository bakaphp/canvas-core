<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class CustomFilterMapper extends CustomMapper
{
    /**
     * @param Baka\Database\CustomFilters\CustomFilters $filter
     * @param \Canvas\Dto\CustomFilter $filterSchema
     * @return ListSchema
     */
    public function mapToObject($filter, $customFilter)
    {
        $customFilter->id = $filter->getId();
        $customFilter->system_modules_id = $filter->system_modules_id;
        $customFilter->companies_id = $filter->companies_id;
        $customFilter->companies_branch_id = $filter->companies_branch_id;
        $customFilter->users_id = $filter->users_id;
        $customFilter->name = $filter->name;
        $customFilter->sequence_logic = $filter->sequence_logic;
        $customFilter->total_conditions = $filter->total_conditions;
        $customFilter->description = $filter->description;
        $customFilter->contidions = $filter->conditions->toArray();

        return $customFilter;
    }
}
