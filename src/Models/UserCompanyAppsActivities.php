<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Http\Exception\InternalServerErrorException;
use Phalcon\Di;

class UserCompanyAppsActivities extends AbstractModel
{
    public int $companies_id;
    public int $company_branches_id;
    public int $apps_id;
    public string $key;
    public ?string $value = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
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
     * Get the value of the settins by it key.
     *
     * @param string $key
     * @param string $value
     */
    public static function get(string $key) : string
    {
        $setting = self::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and key = ?2',
            'bind' => [
                Di::getDefault()->getUserData()->currentCompanyId(),
                Di::getDefault()->getApp()->getId(),
                $key
            ]
        ]);

        if (is_object($setting)) {
            return $setting->value;
        }

        throw new InternalServerErrorException(_('No settings found with this ' . $key));
    }

    /**
     * Set a setting for the given app.
     *
     * @param string $key
     * @param string $value
     */
    public static function set(string $key, $value) : bool
    {
        $activity = self::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1 and key = ?2',
            'bind' => [
                Di::getDefault()->getUserData()->currentCompanyId(),
                Di::getDefault()->getApp()->getId(),
                $key
            ]
        ]);

        if (!is_object($activity)) {
            $activity = new self();
            $activity->companies_id = Di::getDefault()->getUserData()->currentCompanyId();
            $activity->company_branches_id = Di::getDefault()->getUserData()->currentCompanyBranchId();
            $activity->apps_id = Di::getDefault()->getApp()->getId();
            $activity->key = $key;
        }

        $activity->value = $value;
        $activity->saveOrFail();

        return true;
    }
}
