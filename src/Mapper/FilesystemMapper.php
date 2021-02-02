<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Dto\Files;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class FilesystemMapper extends CustomMapper
{
    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     *
     * @return Files
     */
    public function mapToObject($fileEntity, $fileDto, array $context = [])
    {
        $fileDto->id = $fileEntity->getId();
        $fileDto->filesystem_id = $fileEntity->filesystem_id;
        $fileDto->name = $fileEntity->file->name;
        $fileDto->field_name = $fileEntity->field_name;
        $fileDto->url = $fileEntity->file->url;
        $fileDto->size = $fileEntity->file->size;
        $fileDto->file_type = $fileEntity->file->file_type;
        $fileDto->created_at = $fileEntity->created_at;
        $fileDto->attributes = $fileEntity->getAllSettings();

        return $fileDto;
    }
}
