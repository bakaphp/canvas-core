<?php
declare(strict_types=1);

namespace Canvas\Models\Users;

use Canvas\Models\AbstractModel;
use Canvas\Models\Users;

class UsersDeletionRequest extends AbstractModel
{
    public int $users_id;
    public int $apps_id;
    public string $email;
    public string $data;
    public string $request_date;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_deletion_requests');
        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            [
                'alias' => 'users'
            ]
        );
    }
}
