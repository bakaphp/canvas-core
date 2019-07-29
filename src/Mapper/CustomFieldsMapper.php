<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use function Canvas\Core\isJson;
use Phalcon\Mvc\Model\Resultset;

class CustomFieldsMapper extends CustomMapper
{
    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     * @return Files
     */
    public function mapToObject($customField, $customFieldDto, array $context = [])
    {
        $customFieldDto->id = $customField->getId();
        $customFieldDto->users_id = $customField->users_id;
        $customFieldDto->companies_id = $customField->companies_id;
        $customFieldDto->name = $customField->name;
        $customFieldDto->label = $customField->label;
        $customFieldDto->custom_fields_modules_id = $customField->custom_fields_modules_id;
        $customFieldDto->fields_type_id = $customField->fields_type_id;

        $customFieldDto->attributes = !empty($customField->attributes) && isJson($customField->attributes) ? json_decode($customField->attributes) : null;
        $customFieldDto->values = $customField->values ? $this->getValues($customField->values) : null;
        $customFieldDto->type = $customField->type ? $customField->type->toArray() : null;

        $customFieldDto->created_at = $customField->created_at;
        $customFieldDto->updated_at = $customField->updated_at;
        $customFieldDto->is_deleted = $customField->is_deleted;

        return $customFieldDto;
    }

    /**
     * Format the value array of a custom field.
     *
     * @param array $values
     * @return array
     */
    private function getValues(Resultset $values): array
    {
        $newValue = [];
        foreach ($values as $value) {
            $newValue[] = [
                'label' => $value->label,
                'value' => $value->value
            ];
        }

        return $newValue;
    }
}
