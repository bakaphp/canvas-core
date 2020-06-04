<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Contracts\HashTableTrait;
use Canvas\Http\Exception\NotFoundException;
use Exception;
use Phalcon\Di;

/**
 * Classs for FileSystem.
 *
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 * @property \Phalcon\DI $di
 *
 */
class FileSystem extends AbstractModel
{
    use HashTableTrait;

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $system_modules_id = 0;

    /**
     *
     * @var integer
     */
    public $entity_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $path;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var string
     */
    public $size;

    /**
     *
     * @var string
     */
    public $file_type;

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
     * @var int
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('filesystem');

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'system_modules_id',
            'Canvas\Models\SystemModules',
            'id',
            ['alias' => 'systemModules']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\FileSystemSettings',
            'filesystem_id',
            ['alias' => 'attributes']
        );

        $this->hasOne(
            'id',
            'Canvas\Models\FileSystemSettings',
            'filesystem_id',
            ['alias' => 'attribute']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\FileSystemEntities',
            'filesystem_id',
            ['alias' => 'entities']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'filesystem';
    }

    /**
     * Get the element by its entity id.
     *
     * @param string $id
     *
     * @return FileSystem
     * @throw Exception
     */
    public static function getAllByEntityId($id, SystemModules $systemModule)
    {
        //public images
        $condition = 'is_deleted = :is_deleted: AND apps_id = :apps_id: AND id in 
        (SELECT 
            filesystem_id from \Canvas\Models\FileSystemEntities e
            WHERE e.system_modules_id = :system_modules_id: AND e.entity_id = :entity_id:
        )';

        $bind = [
            'is_deleted' => 0,
            'apps_id' => Di::getDefault()->getApp()->getId(),
            'system_modules_id' => $systemModule->getId(),
            'entity_id' => $id
        ];

        if ((bool) Di::getDefault()->get('app')->get('public_images') == false) {
            $condition = 'is_deleted = :is_deleted: AND apps_id = :apps_id: AND companies_id = :companies_id: AND id in 
                (SELECT 
                    filesystem_id from \Canvas\Models\FileSystemEntities e
                    WHERE e.system_modules_id = :system_modules_id: AND e.entity_id = :entity_id:
                )';

            $bind['companies_id'] = Di::getDefault()->getUserData()->currentCompanyId();
        }

        return FileSystem::find([
            'conditions' => $condition,
            'bind' => $bind
        ]);
    }

    /**
     * Get the element by its entity id.
     *
     * @param string $id
     *
     * @return FileSystem
     * @throw Exception
     */
    public static function getById($id) : FileSystem
    {
        //public images
        $conditions = 'id = :id: AND apps_id = :apps_id: AND is_deleted = 0';
        $bind = [
            'id' => $id,
            'apps_id' => Di::getDefault()->getApp()->getId()
        ];

        if ((bool) Di::getDefault()->get('app')->get('public_images') == false) {
            $conditions = 'id = :id: AND companies_id = :companies_id: AND apps_id = :apps_id: AND is_deleted = 0';
            $bind['companies_id'] = Di::getDefault()->getUserData()->currentCompanyId();
        }

        $file = self::findFirst([
            'conditions' => $conditions,
            'bind' => $bind
        ]);

        if (!is_object($file)) {
            throw new NotFoundException('FileSystem ' . (int) $id . '  not found');
        }

        return $file;
    }

    /**
     * Given a new string move the file to that location.
     *
     * @return bool
     */
    public function move(string $location) : bool
    {
        $appSettingFileConfig = $this->di->get('app')->get('filesystem');
        $fileSystemConfig = $this->di->get('config')->filesystem->{$appSettingFileConfig};

        $newPath = $location . '/' . basename($this->path);
        $newUrl = $fileSystemConfig->cdn . DIRECTORY_SEPARATOR . $newPath;

        try {
            $this->di->get('filesystem')->rename($this->path, $newPath);
            $this->path = $newPath;
            $this->url = $newUrl;
            $this->updateOrFail();
        } catch (Exception $e) {
            $this->di->get('log')->info($e->getMessage() . 'For ' . get_class($this) . ' ' . $this->getId());
        }

        return true;
    }
}
