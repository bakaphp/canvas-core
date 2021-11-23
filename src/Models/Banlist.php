<?php
declare(strict_types=1);

namespace Canvas\Models;

class Banlist extends AbstractModel
{
    public int $users_id;
    public string $ip;
    public string $email;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            [
                'alias' => 'user'
            ]
        );
    }
}
