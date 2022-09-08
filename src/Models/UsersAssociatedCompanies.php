<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Model;
use function Baka\isJson;

class UsersAssociatedCompanies extends Model
{
    public int $users_id;
    public int $company_id;
    public int $companies_branches_id = 0;
    public string $identify_id;
    public int $user_active;
    public ?string $configuration = null;
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

    /**
     * Set a new config value for the specific user.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value) : void
    {
        if (isJson($this->configuration)) {
            $configuration = json_decode($this->configuration, true);
            $configuration[$key] = $value;
            $this->configuration = json_encode($configuration);
        } else {
            $this->configuration = json_encode([
                $key => $value
            ]);
        }

        $this->saveOrFail();
    }

    /**
     * Get a specific config value for the specific user.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (isJson($this->configuration)) {
            $configuration = json_decode($this->configuration, true);
            return $configuration[$key] ?? null;
        }

        return null;
    }
}
