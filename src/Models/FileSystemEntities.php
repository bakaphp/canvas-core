<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Canvas\Exception\ModelException;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class FileSystemEntities extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $filesystem_id;

    /**
     *
     * @var integer
     */
    public $entity_id;

    /**
     *
     * @var integer
     */
    public $system_modules_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var string
     */
    public $field_name;

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
        $this->setSource('filesystem_entities');

        $this->belongsTo(
            'filesystem_id',
            'Canvas\Models\Filesystem',
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
                    'message' => $this->filesystem_id.' - Cant be attached a to the same entity twice',
                ]
            )
        );
        return $this->validate($validator);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'filesystem_entities';
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param integer $id
     * @param SystemModules $systemModules
     * @return void
     */
    public static function getByIdWithSystemModule(int $id, SystemModules $systemModules)
    {
        $app = Di::getDefault()->getApp();
        $companyId = Di::getDefault()->getUserData()->currentCompanyId();

        return self::findFirst([
            'conditions' => 'id = ?0 AND  system_modules_id = ?1 AND companies_id = ?2 AND  is_deleted = 0 AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = ?3 AND s.is_deleted = 0)',
            'bind' => [$id, $systemModules->getId(), $companyId, $app->getId()]
        ]);
    }

    /**
     * Get a filesystem entities from this system modules.
     *
     * @param integer $id
     * @param SystemModules $systemModules
     * @return void
     */
    public static function getById(int $id): FileSystemEntities
    {
        $app = Di::getDefault()->getApp();
        $companyId = Di::getDefault()->getUserData()->currentCompanyId();

        $fileEntity = self::findFirst([
            'conditions' => 'id = ?0 AND companies_id = ?1 AND  is_deleted = 0 AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = ?2 AND s.is_deleted = 0)',
            'bind' => [$id, $companyId, $app->getId()]
        ]);

        if (!is_object($fileEntity)) {
            throw new ModelException('File not found');
        }

        return $fileEntity;
    }

    /**
     * Given a entity id get all its asociated files.
     *
     * @param integer $id
     * @return void
     */
    public static function getAllByEntityId(int $id)
    {
        $app = Di::getDefault()->getApp();
        $companyId = Di::getDefault()->getUserData()->currentCompanyId();

        $files = self::find([
            'conditions' => 'entity_id = ?0 AND companies_id = ?1 AND  is_deleted = 0 AND 
                                filesystem_id in (SELECT s.id from \Canvas\Models\FileSystem s WHERE s.apps_id = ?2 AND s.is_deleted = 0)',
            'bind' => [$id, $companyId, $app->getId()]
        ]);

        if (!is_object($files)) {
            return ;
        }

        return $files;
    }
}
