<?php

declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class ResourcesAccesses extends AbstractModel
{
    public int $resources_id;
    public string $resources_name;
    public string $access_name;
    public int $apps_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('resources_accesses');

        $this->belongsTo(
            'resources_id',
            'Canvas\Models\Resources',
            'id',
            ['alias' => 'resources']
        );
    }

    /**
     * Check if it exist.
     *
     * @param Resources $resource
     * @param string $accessName
     *
     * @return integer
     */
    public static function exist(Resources $resource, string $accessName) : int
    {
        return self::count([
            'conditions' => 'resources_id = ?0 AND access_name = ?1 AND apps_id = ?2',
            'bind' => [
                $resource->getId(),
                $accessName,
                Di::getDefault()->getAcl()->getApp()->getId()
            ]
        ]);
    }
}
