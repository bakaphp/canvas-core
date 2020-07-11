<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Http\Exception\NotFoundException;
use Phalcon\Di;

class AccessList extends AbstractModel
{
    public string $roles_name;
    public string $resources_name;
    public string $access_name;
    public int $allowed;
    public int $apps_id;
    public int $roles_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('access_list');

        $this->belongsTo(
            'roles_name',
            'Canvas\Models\Roles',
            'name',
            ['alias' => 'role']
        );
    }

    /**
     * Given the resource and access check if exist.
     *
     * @param Roles $role
     * @param string $resourceName
     * @param string $accessName
     *
     * @return integer
     */
    public static function exist(Roles $role, string $resourceName, string $accessName) : int
    {
        return self::count([
            'conditions' => 'roles_id = ?0 and resources_name = ?1 AND access_name = ?2 AND apps_id = ?3',
            'bind' => [$role->getId(), $resourceName, $accessName, Di::getDefault()->getAcl()->getApp()->getId()]
        ]);
    }

    /**
     * Given the resource and access check if exist.
     *
     * @param Roles $role
     * @param string $resourceName
     * @param string $accessName
     *
     * @return integer
     */
    public static function getBy(Roles $role, string $resourceName, string $accessName) : AccessList
    {
        $access = self::findFirst([
            'conditions' => 'roles_id = ?0 and resources_name = ?1 AND access_name = ?2 AND apps_id = ?3',
            'bind' => [$role->getId(), $resourceName, $accessName, Di::getDefault()->getAcl()->getApp()->getId()]
        ]);

        if (!is_object($access)) {
            throw new NotFoundException(_('Access for role ' . $role->name . ' with resource ' . $resourceName . '-' . $accessName . ' not found on this app ' . Di::getDefault()->getAcl()->getApp()->getId() . ' AND Company' . Di::getDefault()->getAcl()->getCompany()->getId()));
        }

        return $access;
    }
}
