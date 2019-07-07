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
     * constructor.
     *
     * @param integer $entityId
     * @param integer $systemModuleId
     */
    public function __construct(int $entityId, int $systemModuleId)
    {
        $this->systemModuleId = $systemModuleId;
        $this->entityId = $entityId;
    }

    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     * @return Files
     */
    public function mapToObject($file, $fileDto, array $context = [])
    {
        $fileEntity = FileSystemEntities::findFirst([
            'conditions' => 'system_modules_id = ?0 AND entity_id = ?1 AND filesystem_id = ?2 AND companies_id = ?3 AND is_deleted = 0',
            'bind' => [$this->systemModuleId, $this->entityId, $file->getId(), $file->companies_id]
        ]);

        //cant type check 
        if (!is_object($fileEntity) || !is_object($file)) {
            return ;
        }

        $fileDto->id = $fileEntity->getId();
        $fileDto->filesystem_id = $file->getId();
        $fileDto->name = $file->name;
        $fileDto->field_name = $fileEntity ? $fileEntity->field_name : null;
        $fileDto->url = $file->url;
        $fileDto->size = $file->size;
        $fileDto->file_type = $file->file_type;
        $fileDto->attributes = $file->getAllSettings();

        return $fileDto;
    }
}
