<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use function Canvas\Core\isJson;
use Phalcon\Mvc\Model\Resultset;

class CustomFieldsModulesMapper extends CustomMapper
{
    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     * @return Files
     */
    public function mapToObject($customFieldsModules, $customFieldsModulesDto, array $context = [])
    {
        $customFieldsArray = [];
        $customFieldsModulesDto->id = $customFieldsModules->getId();
        $customFieldsModulesDto->apps_id = $customFieldsModules->apps_id;
        $customFieldsModulesDto->name = $customFieldsModules->name;
        $customFieldsModulesDto->created_at = $customFieldsModules->created_at;
        $customFieldsModulesDto->updated_at = $customFieldsModules->updated_at;
        $customFieldsModulesDto->is_deleted = $customFieldsModules->is_deleted;

        foreach ($customFieldsModules->getFields() as $customField) {

            $customFieldsArray['id'] = $customField->getId();
            $customFieldsArray['apps_id'] = $customField->apps_id;
            $customFieldsArray['users_id'] = $customField->users_id;
            $customFieldsArray['companies_id'] = $customField->companies_id;
            $customFieldsArray['name'] =  $customField->name;
            $customFieldsArray['label'] = $customField->label;
            $customFieldsArray['custom_fields_modules_id'] = $customField->custom_fields_modules_id;
            $customFieldsArray['fields_type_id'] = $customField->fields_type_id;

            $customFieldsArray['attributes'] = !empty($customField->attributes) && isJson($customField->attributes) ? json_decode($customField->attributes) : null;
            $customFieldsArray['values'] = $customField->values ? $this->getValues($customField->values) : null;
            $customFieldsArray['type'] = $customField->type ? $customField->type->toArray() : null;

            $customFieldsArray['created_at'] = $customField->created_at;
            $customFieldsArray['updated_at'] = $customField->updated_at;
            $customFieldsArray['is_deleted'] = $customField->is_deleted;

            $customFieldsModulesDto->custom_fields[] = $customFieldsArray;
        }

        //This corresponds to the custom fields
        

        return $customFieldsModulesDto;
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
