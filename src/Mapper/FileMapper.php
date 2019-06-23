<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Dto\Files;
use Canvas\Models\FileSystemEntities;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class FileMapper extends CustomMapper
{
    public $systemModuleId;
    public $entityId;

    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     * @return Files
     */
    public function mapToObject($file, $fileDto, array $context = [])
    {
        $fieledName = FileSystemEntities::findFirst([
            'conditions' => 'system_modules_id = ?0 AND entity_id = ?1 AND filesystem_id = ?2 AND companies_id = ?3',
            'bind' => [$this->systemModuleId, $this->entityId, $file->getId(), $file->companies_id]
        ]);

        $fileDto->id = $file->getId();
        $fileDto->companies_id = $file->companies_id;
        $fileDto->apps_id = $file->apps_id;
        $fileDto->users_id = $file->users_id;
        $fileDto->system_modules_id = $this->systemModuleId;
        $fileDto->entity_id = $this->entityId;
        $fileDto->name = $file->name;
        $fileDto->field_name = $fieledName ? $fieledName->field_name : null;
        $fileDto->path = $file->path;
        $fileDto->url = $file->url;
        $fileDto->size = $file->size;
        $fileDto->file_type = $file->file_type;
        $fileDto->created_at = $file->created_at;
        $fileDto->updated_at = $file->updated_at;
        $fileDto->is_deleted = $file->is_deleted;
        $fileDto->attributes = $file->getAllSettings();

        return $fileDto;
    }
}
