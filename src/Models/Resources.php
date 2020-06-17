<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Exception\ModelNotFoundException;
use Phalcon\Di;

class Resources extends AbstractModel
{
    public string $name;
    public ?string $description = null;
    public int $apps_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('resources');

        $this->hasMany(
            'id',
            'Canvas\Models\ResourcesAccesses',
            'resources_id',
            [
                'alias' => 'accesses',
                'params' => [
                    'conditions' => 'apps_id = ' . $this->di->getApp()->getId()
                ]
            ]
        );
    }

    /**
     * is this name a resource?
     *
     * @param string $resourceName
     *
     * @return boolean
     */
    public static function isResource(string $resourceName) : bool
    {
        return (bool) self::count([
            'conditions' => 'name = ?0 AND apps_id in (?1, ?2)',
            'bind' => [
                $resourceName,
                Di::getDefault()->getApp()->getId(),
                Apps::CANVAS_DEFAULT_APP_ID
            ]
        ]);
    }

    /**
     * Get a resource by it name.
     *
     * @param  string  $resourceName
     *
     * @return Resources
     */
    public static function getByName(string $resourceName) : Resources
    {
        $resource = self::findFirst([
            'conditions' => 'name = ?0 AND apps_id in (?1, ?2)',
            'bind' => [
                $resourceName,
                Di::getDefault()->getApp()->getId(),
                Apps::CANVAS_DEFAULT_APP_ID
            ]
        ]);

        if (!is_object($resource)) {
            throw new ModelNotFoundException(
                _('Resource ' . $resourceName . ' not found on this app ' . Di::getDefault()->getApp()->getId())
            );
        }

        return $resource;
    }
}
