<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Phalcon\Validation\Validator\Url;
use Phalcon\Validation;

class UserWebhooks extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $webhooks_id;

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
     * @var integer
     */
    public $companies_id;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var string
     */
    public $method;

    /**
     *
     * @var string
     */
    public $format;

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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('user_webhooks');

        $this->belongsTo(
            'webhooks_id',
            'Canvas\Models\Webhooks',
            'id',
            ['alias' => 'webhook']
        );

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

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
     * Validate input data.
     *
     * @return void
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'url',
            new Url(
                [
                    'message' => 'This Url is not valid',
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
    * Get element by Id.
    *
    * @return Webhooks
    */
    public static function getById($id): self
    {
        return self::findFirstOrFail([
            'conditions' => 'id = ?0 AND apps_id = ?1 AND companies_id = ?2 and is_deleted = 0',
            'bind' => [
                $id,
                Di::getDefault()->getApp()->getId(),
                Di::getDefault()->getUserData()->getDefaultCompany()->getId()
            ]
        ]);
    }
}
