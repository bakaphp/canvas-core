<?php

declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\SystemModules as BakaSystemModules;
use Baka\Http\Exception\InternalServerErrorException;
use Canvas\Contracts\CustomFields\CustomFieldsTrait;
use Phalcon\Di;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Utils\Slug;

class SystemModules extends BakaSystemModules
{
    use CustomFieldsTrait;

    public ?string $bulk_actions = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->hasMany(
            'id',
            EmailTemplatesVariables::class,
            'system_modules_id',
            ['alias' => 'templateVariable']
        );

        $this->hasMany(
            'id',
            Webhooks::class,
            'system_modules_id',
            ['alias' => 'webhook']
        );

        $this->belongsTo(
            'companies_id',
            Companies::class,
            'id',
            ['alias' => 'company', 'reusable' => true]
        );

        $this->belongsTo(
            'apps_id',
            Apps::class,
            'id',
            ['alias' => 'app', 'reusable' => true]
        );

        $this->belongsTo(
            'company_branches_id',
            CompaniesBranches::class,
            'id',
            ['alias' => 'companyBranch', 'reusable' => true]
        );
    }

    /**
     * Get System Module by its model_name.
     *
     * @deprecated v2
     *
     * @param string $model_name
     * @param bool $cache
     *
     * @return SystemModules
     */
    public static function getSystemModuleByModelName(string $modelName, bool $cache = true) : self
    {
        return self::getByModelName($modelName);
    }

    /**
     * Get System Module by its model_name.
     *
     * @param string $model_name
     *
     * @return SystemModules
     */
    public static function getByModelName(string $modelName) : self
    {
        $app = Di::getDefault()->get('app');
        $params = [
            'conditions' => 'model_name = ?0 and apps_id = ?1',
            'bind' => [
                $modelName,
                $app->getId()
            ]
        ];

        $module = self::findFirst($params);

        if (!is_object($module)) {
            throw new InternalServerErrorException('No system module found for ' . $modelName);
        }

        return $module;
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
     * Create a new system module for the given app.
     *
     * @param string $class
     * @param Apps $app
     *
     * @return self
     */
    public static function createForApp(string $class, Apps $app) : self
    {
        if (!class_exists($class)) {
            throw new InternalServerErrorException('We expecting the complete class with namespace');
        }

        return SystemModules::updateOrCreate([
            'conditions' => 'model_name = :model_name:
                            AND apps_id = :apps_id:',
            'bind' => [
                'model_name' => $class,
                'apps_id' => $app->getId()
            ],
        ], [
            'name' => $class,
            'model_name' => $class,
            'apps_id' => $app->getId(),
            'slug' => Slug::generate($class),
        ]);
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
