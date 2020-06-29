<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use function Canvas\Core\isJson;
use Phalcon\Mvc\Model\Resultset;

class MenusMapper extends CustomMapper
{
    /**
     * @param Canvas\Models\FileSystem $file
     * @param Canvas\Dto\Files $fileDto
     *
     * @return Files
     */
    public function mapToObject($menus, $menusDto, array $context = [])
    {
        $menusDto->id = $menus->getId();
        $menusDto->apps_id = $menus->apps_id;
        $menusDto->companies_id = $menus->companies_id;
        $menusDto->name = $menus->name;
        $menusDto->slug = $menus->slug;
        $menusDto->sidebar = $menus->getLinks();
        $menusDto->created_at = $menus->created_at;
        $menusDto->updated_at = $menus->updated_at;
        $menusDto->is_deleted = $menus->is_deleted;

        return $menusDto;
    }
}
