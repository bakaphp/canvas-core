<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\SystemModules as BakaSystemModules;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Contracts\CustomFields\CustomFieldsTrait;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;

class SystemModules extends BakaSystemModules
{
    use CustomFieldsTrait;

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
     * Get System Module by its model_name.
     *
     * @deprecated v2
     *
     * @param string $model_name
     *
     * @return ModelInterface
     */
    public static function getSystemModuleByModelName(string $modelName) : ModelInterface
    {
        $app = Di::getDefault()->get('app');
        $module = SystemModules::findFirst([
            'conditions' => 'model_name = ?0 and apps_id = ?1',
            'bind' => [
                $modelName,
                $app->getId()
            ],
            'cache' => [
                'key' => 'SYSTEM_MODULE_' . $app->getId() . '_' . $modelName,
                'lifetime' => 386400
            ]
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
     *
     * @return ModelInterface
     */
    public static function getByModelName(string $modelName) : ModelInterface
    {
        return self::getSystemModuleByModelName($modelName);
    }

    /**
     * Get System Module by Name.
     *
     * @param string $name
     *
     * @return ModelInterface
     */
    public static function getByName(string $name) : ModelInterface
    {
        return self::findFirstOrFail([
            'conditions' => 'name = ?0 and apps_id = ?1',
            'bind' => [
                $name,
                Di::getDefault()->get('app')->getId()
            ]
        ]);
    }

    /**
     * Get System Module by id.
     *
     * @param int $id
     *
     * @return ModelInterface
     */
    public static function getById($id) : ModelInterface
    {
        $module = SystemModules::findFirstOrFail([
            'conditions' => 'id = ?0 and apps_id = ?1',
            'bind' => [
                $id,
                Di::getDefault()->get('app')->getId()
            ]
        ]);

        return $module;
    }

    /**
     * Get System Module by id.
     *
     * @param int $id
     *
     * @return ModelInterface
     */
    public static function getBySlug(string $slug) : ModelInterface
    {
        $module = SystemModules::findFirstOrFail([
            'conditions' => 'slug = ?0 and apps_id = ?1',
            'bind' => [
                $slug,
                Di::getDefault()->get('app')->getId()
            ]
        ]);

        return $module;
    }

    /**
     * Given tell them if this system module is index in elastic.
     *
     * @return bool
     */
    public function useElastic() : bool
    {
        return (bool) $this->use_elastic;
    }
}
