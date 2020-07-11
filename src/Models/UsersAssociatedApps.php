<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Auth\UserInterface;

class UsersAssociatedApps extends AbstractModel implements UserInterface
{
    public int $users_id;
    public int $apps_id;
    public int $companies_id;
    public string $identify_id;
    public int $user_active;
    public string $user_role;
    public ?string $password = null;

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
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->setSource('users_associated_apps');
    }
}
