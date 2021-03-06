<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;
use Phalcon\Di;

class UserCompanyApps extends Model
{
    public int $company_id;
    public int $companies_id;
    public int $apps_id;
    public ?string $stripe_id = null;
    public ?int $subscriptions_id = 0;

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
            'bind' => [
                Di::getDefault()->get('userData')->currentCompanyId(),
                Di::getDefault()->get('app')->getId()
            ]
        ]);
    }
}
