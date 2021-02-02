<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Dto\Filesystem;

// You can either extend the CustomMapper, or just implement the MapperInterface
// directly.
class FileSystemMapper extends CustomMapper
{
    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     *
     * @return Files
     */
    public function mapToObject($filesystem, $filesystemDto, array $context = [])
    {
        $filesystemDto->id = $filesystem->getId();
        $filesystemDto->companies_id = $filesystem->companies_id;
        $filesystemDto->apps_id = $filesystem->apps_id;
        $filesystemDto->users_id = $filesystem->users_id;
        $filesystemDto->name = $filesystem->name;
        $filesystemDto->path = $filesystem->path;
        $filesystemDto->url = $filesystem->url;
        $filesystemDto->size = $filesystem->size;
        $filesystemDto->file_type = $filesystem->file_type;
        $filesystemDto->created_at = $filesystem->created_at;
        $filesystemDto->attributes = $filesystem->getAllSettings();

        return $filesystemDto;
    }
}
