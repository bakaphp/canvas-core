<?php

declare(strict_types=1);

namespace Canvas\Mapper\Notifications;

use AutoMapperPlus\CustomMapper\CustomMapper;

class UserImportance extends CustomMapper
{

    /**
     * Map.
     *
     * @param \Canvas\Models\Notifications\UserEntityImportance $userEntityImportance
     * @param \Canvas\Dto\Notifications\UserImportance $userImportanceDto
     * @param array $context
     *
     * @return mixed
     */
    public function mapToObject($userEntityImportance, $userImportanceDto, array $context = [])
    {
        $userImportanceDto->entity_id = $userEntityImportance->entity_id;
        $userImportanceDto->system_modules = [
            'id' => $userEntityImportance->system_modules_id,
            'name' => $userEntityImportance->systemModule->name,
        ];
        $userImportanceDto->importance_id = $userEntityImportance->importance_id;

        return $userImportanceDto;
    }
}
