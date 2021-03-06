<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Contracts\Database\HashTableTrait;
use Baka\Database\Model;

class UserConfig extends Model
{
    use HashTableTrait;

    public ?int $users_id = null;
    public ?string $name = null;
    public ?string $value = null;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('user_config');
        $this->belongsTo('users_id', Users::class, 'id', ['alias' => 'user']);
    }
}
