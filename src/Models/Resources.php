<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Exception\ModelNotFoundException;
use Phalcon\Di;
use Canvas\Exception\ModelException;

/**
 * Class Resources.
 *
 * @package Canvas\Models
 *
 * @property \Phalcon\Di $di
 */
class Resources extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     *
     * @var integer
     */
    public $is_deleted;

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

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'resources';
    }
}
