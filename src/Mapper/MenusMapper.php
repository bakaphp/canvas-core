<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use Canvas\Models\MenusLinks;

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
        $menusDto->name = $menus->name;
        $menusDto->slug = $menus->slug;

        foreach ($menus->getLinks() as $link) {
            if ($link->isParent()) {
                $childLinks = MenusLinks::find([
                    'conditions' => 'parent_id = ?0 and is_deleted = 0',
                    'bind' => [$link->getId()]
                ]);

                if (sizeof($childLinks) != 0) {
                    $childLinksArray = [];
                    $childLinksArray['title'] = $link->title;
                    $childLinksArray['links'] = [];

                    foreach ($childLinks as $childLink) {
                        $childArray = $this->convertObjectToArray($childLink);
                        $childArray['slug'] = $childLink->getModules() ?? $childLink->getModules()->slug;
                        $childLinksArray['links'][] = $childArray;
                    }

                    $menusDto->sidebar[] = $childLinksArray;
                } else {
                    $linkArray = $this->convertObjectToArray($link);
                    $linkArray['slug'] = $link->getModules()->slug;
                    $menusDto->sidebar[] = $linkArray;
                }
            } elseif ($link->menus_id != 0 && $link->isParent()) {
                $menusDto->sidebar[] = $link;
            }
        }

        $menusDto->created_at = $menus->created_at;
        $menusDto->updated_at = $menus->updated_at;
        $menusDto->is_deleted = $menus->is_deleted;

        return $menusDto;
    }

    /**
     * Convert object to array.
     *
     * @param object $object
     *
     * @return array
     */
    private function convertObjectToArray(object $object) : array
    {
        $array = [];
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }
}
