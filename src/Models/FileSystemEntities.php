<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class FileSystemEntities extends AbstractModel
{
    public int $filesystem_id = 0;

    /**
     * @todo for php 8.1 update type
     *
     * @var int|string
     */
    public $entity_id;
    public int $system_modules_id = 0;
    public int $companies_id = 0;
    public ?string $field_name = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('filesystem_entities');

        $this->belongsTo(
            'filesystem_id',
            FileSystem::class,
            'id',
            [
                'alias' => 'file',
                'reusable' => true
            ]
        );
    }

    /**
     * Validate the model.
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            ['filesystem_id', 'entity_id', 'system_modules_id'],
            new Uniqueness(
                [
                    'message' => $this->filesystem_id . ' - Cant be attached a to the same entity twice',
                ]
            )
        );
        return $this->validate($validator);
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param int $id
     * @param SystemModules $systemModules
     * @param bool $isDeleted deprecated
     *
     * @return FileSystemEntities
     */
    public static function getByIdWithSystemModule(int $id, SystemModules $systemModules, bool $isDeleted = false)
    {
        $app = Di::getDefault()->get('app');
        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'system_modules_id' => $systemModules->getId(),
            'apps_id' => $app->getId(),
        ];

        if (!(bool) $app->get('public_images')) {
            $companyId = Di::getDefault()->get('userData')->currentCompanyId();
            $addCompanySql = 'AND companies_id = :companies_id:';
            $bind['companies_id'] = $companyId;
        }

        return self::findFirst([
            'conditions' => 'id = :id: AND system_modules_id = :system_modules_id: ' . $addCompanySql . '  AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = :apps_id: )',
            'bind' => $bind
        ]);
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param int $id
     * @param SystemModules $systemModules
     *
     * @return FileSystemEntities
     */
    public static function getById(int $id) : FileSystemEntities
    {
        $app = Di::getDefault()->get('app');

        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->get('app')->get('public_images')) {
            $companyId = Di::getDefault()->get('userData')->currentCompanyId();
            $addCompanySql = 'AND companies_id = :companies_id:';
            $bind['companies_id'] = $companyId;
        }

        return self::findFirstOrFail([
            'conditions' => 'id = :id: ' . $addCompanySql . ' AND  is_deleted = 0 AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = :apps_id: AND s.is_deleted = 0)',
            'bind' => $bind
        ]);
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param int $id
     * @param SystemModules $systemModules
     *
     * @return FileSystemEntities
     */
    public static function getByEntityId(int $id) : FileSystemEntities
    {
        $app = Di::getDefault()->get('app');

        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->get('app')->get('public_images')) {
            $companyId = Di::getDefault()->get('userData')->currentCompanyId();
            $addCompanySql = 'AND companies_id = :companies_id:';
            $bind['companies_id'] = $companyId;
        }

        return self::findFirstOrFail([
            'conditions' => 'entity_id = :id: ' . $addCompanySql . ' AND is_deleted = 0 AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = :apps_id: AND s.is_deleted = 0)',
            'bind' => $bind
        ]);
    }

    /**
     * Given a entity id get all its associated files.
     *
     * @param int $id
     * @param SystemModules $systemModule
     *
     * @return FileSystemEntities[]
     */
    public static function getAllByEntityId(int $id, ?SystemModules $systemModule = null)
    {
        $app = Di::getDefault()->get('app');

        $addSql = null;

        $bind = [
            'id' => $id,
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->get('app')->get('public_images')) {
            $companyId = Di::getDefault()->get('userData')->currentCompanyId();
            $addSql = 'AND companies_id = :companies_id:';
            $bind['companies_id'] = $companyId;
        }

        if ($systemModule instanceof SystemModules) {
            $addSql .= ' AND system_modules_id = :system_modules_id:';
            $bind['system_modules_id'] = $systemModule->getId();
        }

        $files = self::find([
            'conditions' => 'entity_id = :id: ' . $addSql . ' AND  is_deleted = 0 
                            AND filesystem_id in (
                                SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = :apps_id: AND s.is_deleted = 0
                            )',
            'bind' => $bind
        ]);

        if (!is_object($files)) {
            return ;
        }

        return $files;
    }
}
