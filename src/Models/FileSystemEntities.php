<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class FileSystemEntities extends AbstractModel
{
    public int $filesystem_id;
    public int $entity_id;
    public int $system_modules_id;
    public int $companies_id;
    public string $field_name;

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
            ['alias' => 'file']
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
        $app = Di::getDefault()->getApp();
        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'system_modules_id' => $systemModules->getId(),
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->getApp()->get('public_images')) {
            $companyId = Di::getDefault()->getUserData()->currentCompanyId();
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
        $app = Di::getDefault()->getApp();

        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->getApp()->get('public_images')) {
            $companyId = Di::getDefault()->getUserData()->currentCompanyId();
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
        $app = Di::getDefault()->getApp();

        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->getApp()->get('public_images')) {
            $companyId = Di::getDefault()->getUserData()->currentCompanyId();
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
     * Given a entity id get all its asociated files.
     *
     * @param int $id
     *
     * @return FileSystemEntities[]
     */
    public static function getAllByEntityId(int $id)
    {
        $app = Di::getDefault()->getApp();

        $addCompanySql = null;

        $bind = [
            'id' => $id,
            'apps_id' => $app->getId(),
        ];

        if (!(bool) Di::getDefault()->getApp()->get('public_images')) {
            $companyId = Di::getDefault()->getUserData()->currentCompanyId();
            $addCompanySql = 'AND companies_id = :companies_id:';
            $bind['companies_id'] = $companyId;
        }

        $files = self::find([
            'conditions' => 'entity_id = :id: ' . $addCompanySql . ' AND  is_deleted = 0 AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = :apps_id: AND s.is_deleted = 0)',
            'bind' => $bind
        ]);

        if (!is_object($files)) {
            return ;
        }

        return $files;
    }
}
