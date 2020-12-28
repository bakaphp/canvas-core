<?php

namespace Canvas\Models;

use Baka\Database\Model;

class SessionKeys extends Model
{
    public string $sessions_id;
    public int $users_id;
    public string $last_ip;
    public string $last_login;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->belongsTo('sessions_id', Sessions::class, 'id', ['alias' => 'session']);
    }
}
