<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Models\Subscription;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class EmailTemplatesMapper extends CustomMapper
{
    /**
     * @param Baka\Database\CustomFilters\CustomFilters $filter
     * @param \Canvas\Dto\CustomFilter $filterSchema
     * @return ListSchema
     */
    public function mapToObject($emailTemplates, $emailTemplatesDto, array $context = [])
    {
        $emailTemplatesDto->id = $emailTemplates->getId();
        $emailTemplatesDto->companies_id = $emailTemplates->companies_id;
        $emailTemplatesDto->app_id = $emailTemplates->app_id;
        $emailTemplatesDto->name = $emailTemplates->name;
        $emailTemplatesDto->template = urldecode($emailTemplates->template) ?: $emailTemplates->template;
        $emailTemplatesDto->users_id = $emailTemplates->users_id;
        $emailTemplatesDto->created_at = $emailTemplates->created_at;
        $emailTemplatesDto->updated_at = $emailTemplates->updated_at;
        $emailTemplatesDto->is_deleted = $emailTemplates->is_deleted;

        return $emailTemplatesDto;
    }
}
