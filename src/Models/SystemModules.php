<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Canvas\Exception\ModelException;

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

        $this->setSource('user_company_apps_activities');
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
     * @param string $model_name
     * @return SystemModules
     */
    public static function getSystemModuleByModelName(string $modelName): SystemModules
    {
        $module = SystemModules::findFirst([
            'conditions' => 'model_name = ?0 and apps_id = ?1',
            'bind' => [$modelName, Di::getDefault()->getApp()->getId()]
        ]);

        if (!is_object($module)) {
            throw new ModelException('No system module for ' . $modelName);
        }

        return $module;
    }

    /**
     * Get System Module by id.
     *
     * @param int $id
     * @return SystemModules
     */
    public static function getById($id): SystemModules
    {
        $module = SystemModules::findFirst([
            'conditions' => 'id = ?0 and apps_id = ?1',
            'bind' => [$id, Di::getDefault()->getApp()->getId()]
        ]);

        if (!is_object($module)) {
            throw new ModelException('System Module not found');
        }

        return $module;
    }
}
