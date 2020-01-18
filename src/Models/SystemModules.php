<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Canvas\Http\Exception\InternalServerErrorException;
use Phalcon\Mvc\ModelInterface;

class SystemModules extends AbstractModel
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
    public $name;

    /**
     *
     * @var integer
     */
    public $slug;

    /**
     *
     * @var string
     */
    public $model_name;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $parents_id;

    /**
     *
     * @var integer
     */
    public $menu_order;

    /**
     *
     * @var integer
     */
    public $use_elastic;

    /**
     *
     * @var string
     */
    public $browse_fields;

    /**
     *
     * @var integer
     */
    public $show;

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
        $this->hasMany(
            'id',
            'Canvas\Models\EmailTemplatesVariables',
            'system_modules_id',
            ['alias' => 'templateVariable']
        );

        $this->hasMany(
            'id',
            'Canvas\Models\Webhooks',
            'system_modules_id',
            ['alias' => 'webhook']
        );

        $this->belongsTo(
            'companies_id',
            'Canvas\Models\Companies',
            'id',
            ['alias' => 'company']
        );

        $this->belongsTo(
            'apps_id',
            'Canvas\Models\Apps',
            'id',
            ['alias' => 'app']
        );

        $this->belongsTo(
            'company_branches_id',
            'Canvas\Models\CompanyBranches',
            'id',
            ['alias' => 'companyBranch']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'system_modules';
    }

    /**
     * Get System Module by its model_name.
     *
     * @deprecated v2
     * @param string $model_name
     * @return ModelInterface
     */
    public static function getSystemModuleByModelName(string $modelName): ModelInterface
    {
        $module = SystemModules::findFirst([
            'conditions' => 'model_name = ?0 and apps_id = ?1',
            'bind' => [$modelName, Di::getDefault()->getApp()->getId()]
        ]);

        if (!is_object($module)) {
            throw new InternalServerErrorException('No system module for ' . $modelName);
        }

        return $module;
    }

    /**
     * Get System Module by its model_name.
     *
     * @param string $model_name
     * @return ModelInterface
     */
    public static function getByModelName(string $modelName): ModelInterface
    {
        return self::getSystemModuleByModelName($modelName);
    }

    /**
     * Get System Module by Name.
     *
     * @param string $name
     * @return ModelInterface
     */
    public static function getByName(string $name): ModelInterface
    {
        return self::getSystemModuleByName($name);
    }

    /**
     * Get System Module by id.
     *
     * @param int $id
     * @return ModelInterface
     */
    public static function getById($id): ModelInterface
    {
        $module = SystemModules::findFirstOrFail([
            'conditions' => 'id = ?0 and apps_id = ?1',
            'bind' => [$id, Di::getDefault()->getApp()->getId()]
        ]);

        return $module;
    }

    /**
     * Get System Module by id.
     *
     * @param int $id
     * @return ModelInterface
     */
    public static function getBySlug(string $slug): ModelInterface
    {
        $module = SystemModules::findFirstOrFail([
            'conditions' => 'slug = ?0 and apps_id = ?1',
            'bind' => [$slug, Di::getDefault()->getApp()->getId()]
        ]);

        return $module;
    }

    /**
     * Given tell them if this system module is index in elastic.
     *
     * @return bool
     */
    public function useElastic(): bool
    {
        return (bool) $this->use_elastic;
    }
}
