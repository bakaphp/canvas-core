<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;

class UserCompanyApps extends \Baka\Auth\Models\UserCompanyApps
{
    public int $companies_id;
    public int $apps_id;
    public string $stripe_id;
    public int $subscriptions_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();

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

        $this->setSource('user_company_apps');
    }

    /**
     * Get the current company app.
     *
     * @return void
     */
    public static function getCurrentApp() : UserCompanyApps
    {
        return self::findFirst([
            'conditions' => 'companies_id = ?0 and apps_id = ?1',
            'bind' => [Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getApp()->getId()]
        ]);
    }
}
