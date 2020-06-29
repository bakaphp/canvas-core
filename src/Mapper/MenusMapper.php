<?php

declare(strict_types=1);

namespace Canvas\Mapper;

use AutoMapperPlus\CustomMapper\CustomMapper;
use function Canvas\Core\isJson;
use Phalcon\Mvc\Model\Resultset;
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
        $menusDto->companies_id = $menus->companies_id;
        $menusDto->name = $menus->name;
        $menusDto->slug = $menus->slug;

        foreach ($menus->getLinks() as $link) {

            if($link->parent_id == 0)
            {
                $childLinks = MenusLinks::find([
                    'conditions' => 'parent_id = ?0 and is_deleted = 0',
                    'bind' => [$link->getId()]
                ]);
    
                if(sizeof($childLinks) != 0){
                    $childLinksArray = [];
                    $childLinksArray['title'] = $link->title;
                    $childLinksArray['links'] = $childLinks;
                    $menusDto->sidebar[] = $childLinksArray;
                }
                else {
                    $menusDto->sidebar[] = $link;
                }

            }else if($link->menus_id != 0) {
                $menusDto->sidebar[] = $link;
            }
        }

        $menusDto->created_at = $menus->created_at;
        $menusDto->updated_at = $menus->updated_at;
        $menusDto->is_deleted = $menus->is_deleted;

        return $menusDto;
    }
}
