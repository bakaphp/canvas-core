<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Database\HashTableTrait;
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

    public int $companies_id;
    public int $apps_id;
    public int $users_id;
    public int $system_modules_id = 0;
    public int $entity_id;
    public string $name;
    public string $path;
    public string $url;
    public string $size;
    public string $file_type;

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
     * Get the element by its entity id.
     *
     * @param string $id
     *
     * @return FileSystem
     * @throw Exception
     */
    public static function getAllByEntityId($id, SystemModules $systeModule)
    {
        return FileSystem::find([
            'conditions' => '
                is_deleted = ?0 AND apps_id = ?1 AND companies_id = ?2 AND id in 
                    (SELECT 
                        filesystem_id from \Canvas\Models\FileSystemEntities e
                        WHERE e.system_modules_id = ?3 AND e.entity_id = ?4
                    )',
            'bind' => [
                0,
                Di::getDefault()->getApp()->getId(),
                Di::getDefault()->getUserData()->currentCompanyId(),
                $systeModule->getId(),
                $id
            ]
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
        $file = self::findFirst([
            'conditions' => 'id = ?0 AND companies_id = ?1 AND apps_id = ?2 AND is_deleted = 0',
            'bind' => [
                $id,
                Di::getDefault()->getUserData()->currentCompanyId(),
                Di::getDefault()->getApp()->getId()
            ]
        ]);

        if (!is_object($file)) {
            throw new NotFoundException('FileSystem ' . (int) $id . '  not found');
        }

        return $file;
    }

    /**
     * Given a new string move the file to that localtion.
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
