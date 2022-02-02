<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;

class UsersAssociatedCompanies extends Model
{
    public int $users_id;
    public int $company_id;
    public int $companies_branches_id = 0;
    public string $identify_id;
    public int $user_active;
    public string $user_role;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            ['alias' => 'user']
        );

        $this->belongsTo(
            'companies_id',
            Companies::class,
            'id',
            ['alias' => 'company']
        );

        $this->setSource('users_associated_company');
    }
}
