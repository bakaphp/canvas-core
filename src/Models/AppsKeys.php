<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Http\Exception\UnauthorizedException;

/**
 * Class CompanyBranches.
 *
 * @package Canvas\Models
 *
 */
class AppsKeys extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $client_id;

    /**
     *
     * @var integer
     */
    public $client_secret_id;

    /**
     *
     * @var integer
     */
    public $apps_id;

    /**
     *
     * @var integer
     */
    public $users_id;

    /**
     *
     * @var string
     */
    public $last_used_date;

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
     *
     * @var integer
     */
    public $is_deleted;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('apps_keys');

        $this->belongsTo(
            'users_id',
            Users::class,
            'id',
            ['alias' => 'users']
        );

        $this->belongsTo(
            'apps_id',
            Apps::class,
            'id',
            ['alias' => 'apps']
        );
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource() : string
    {
        return 'apps_keys';
    }

    /**
     * Validate Apps Keys by client id, client secret id and apps key
     * @param string $clientId
     * @param string $clientSecretId
     * @param int $appsId
     * 
     * @return AppsKeys
     */
    public static function validateAppsKeys(string $clientId, string $clientSecretId, int $appsId): self
    {
        $appkeys = AppsKeys::findFirst([
            'conditions' => 'client_id = ?0 and client_secret_id = ?1 and apps_id = ?2 and is_deleted = 0',
            'bind' => [$clientId, $clientSecretId, $appsId]
        ]);

        if (!$appkeys){
            throw new UnauthorizedException("Wrong Client Id or Client Secret Id given");
        }

        $appkeys->last_used_date = date('Y-m-d H:i:s');
        $appkeys->update();

        return $appkeys;
    }
}
