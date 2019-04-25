<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Traits\ModelSettingsTrait;
use Canvas\Exception\ModelException;
use Phalcon\Di;

/**
 * Classs for FileSystem.
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 * @property \Phalcon\DI $di
 *
 */
class FileSystem extends AbstractModel
{
    use ModelSettingsTrait;

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
    public $system_modules_id;

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
            'Canvas\Models\FilesystemSettings',
            'filesystem_id',
            ['alias' => 'attributes']
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
     * @return FileSystem
     * @throw Exception
     */
    public static function getByEntityId($id)
    {
        $file = self::findFirst([
            'conditions' => 'entity_id = ?0 and companies_id = ?1 and apps_id = ?2',
            'bind' => [$id, Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getConfig()->app->id]
        ]);

        if (!is_object($file)) {
            throw new ModelException('File not found');
        }

        return $file;
    }
    
    /**
     * Get the element by its entity id.
     *
     * @param string $id
     * @return FileSystem
     * @throw Exception
     */
    public static function getById($id)
    {
        $file = self::findFirst([
            'conditions' => 'id = ?0 and companies_id = ?1 and apps_id = ?2',
            'bind' => [$id, Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getConfig()->app->id]
        ]);

        if (!is_object($file)) {
            throw new ModelException('File not found');
        }

        return $file;
    }
}
