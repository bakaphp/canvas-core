<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Traits\SubscriptionPlanLimitTrait;

class UsersInvite extends AbstractModel
{
    use SubscriptionPlanLimitTrait;

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $invite_hash;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var integer
     */
    public $role_id;

    /**
     *
     * @var integer
     */
    public $app_id;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var integer
     */
    public $is_deleted;

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
     * Subscription plan key
     */
    protected $subscriptionPlanLimitKey = 'users';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_invite');

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
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'users_invite';
    }

    /**
     * What to do after the creation of a new users
     *  - Assign default role
     *
     * @return void
     */
    public function afterCreate()
    {
        $this->updateAppActivityLimit();
    }
}
