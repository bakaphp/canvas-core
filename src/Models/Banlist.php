<?php

namespace Canvas\Models;

use Baka\Database\Model;

class Banlist extends Model
{
    public int $users_id;
    public string $ip;
    public string $email;

    /**
     * Initialize.
     */
    public function initialize()
    {
        $this->belongsTo('users_id', 'Canvas\Models\Users', 'id', ['alias' => 'user']);
    }
}
