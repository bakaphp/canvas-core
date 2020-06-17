<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Url;

class UserWebhooks extends AbstractModel
{
    public int $webhooks_id;
    public int $apps_id;
    public int $users_id;
    public int $companies_id;
    public string $url;
    public string $method;
    public string $format;

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
    public static function getById($id) : self
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
