<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Exception\UnprocessableEntityHttpException;
use Phalcon\Di;

/**
 * Classs for Email Templates
 * @property Users $userData
 * @property Request $request
 * @property Config $config
 * @property Apps $app
 * @property \Phalcon\DI $di
 *
 */
class EmailTemplates extends AbstractModel
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $companies_id;

    /**
     *
     * @var integer
     */
    public $app_id;

    /**
     *
     * @var integer
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $template;

    /**
     *
     * @var string
     */
    public $users_id;

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

        $this->belongsTo(
            'users_id',
            'Canvas\Models\Users',
            'id',
            ['alias' => 'user']
        );

        $this->setSource('email_templates');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'email_templates';
    }

    /**
     * Retrieve email template by name
     * @param $name
     * @return EmailTemplates
     */
    public static function getByName(string $name): EmailTemplates
    {
        $emailTemplate = self::findFirst([
            'conditions' => 'companies_id in (?0, 0) and apps_id in (?1, 0) and name = ?2 and is_deleted = 0',
            'bind' => [Di::getDefault()->getUserData()->currentCompanyId(), Di::getDefault()->getConfig()->app->id, $name]
        ]);

        if (!is_object($emailTemplate)) {
            throw new UnprocessableEntityHttpException(_('No template ' . $name . ' found for this app ' . Di::getDefault()->getApp()->name));
        }

        return $emailTemplate;
    }
}
