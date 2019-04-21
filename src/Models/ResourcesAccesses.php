<?php

declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Canvas\Exception\ModelException;

class ResourcesAccesses extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $resources_id;

    /**
     *
     * @var string
     */
    public $resources_name;

    /**
     *
     * @var string
     */
    public $access_name;

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
        $this->setSource('resources_accesses');

        $this->belongsTo(
            'resources_id',
            'Canvas\Models\Resources',
            'id',
            ['alias' => 'resources']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'resources_accesses';
    }

    /**
     * Check if it exist
     *
     * @param Resources $resouce
     * @param string $accessName
     * @return integer
     */
    public static function exist(Resources $resource, string $accessName) : int
    {
        return self::count([
            'conditions' => 'resources_id = ?0 AND access_name = ?1 AND apps_id = ?2',
            'bind' => [$resource->getId(), $accessName, Di::getDefault()->getAcl()->getApp()->getId()]
        ]);
    }
}
