<?php

declare(strict_types=1);

namespace Canvas\Mapper\Notifications;

use AutoMapperPlus\CustomMapper\CustomMapper;

class Importance extends CustomMapper
{

    /**
     * Map.
     *
     * @param \Canvas\Models\Notifications\Importance $importance
     * @param \Canvas\Dto\Notifications\importance $importanceDto
     * @param array $context
     *
     * @return mixed
     */
    public function mapToObject($importance, $importanceDto, array $context = [])
    {
        $importanceDto->id = $importance->getId();
        $importanceDto->name = $importance->name;

        return $importanceDto;
    }
}
