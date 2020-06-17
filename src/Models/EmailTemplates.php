<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Http\Exception\UnprocessableEntityException;
use Phalcon\Di;

class EmailTemplates extends AbstractModel
{
    public int $companies_id;
    public int $app_id;
    public string $name;
    public string $template;
    public int $users_id;

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
     * Retrieve email template by name.
     *
     * @param $name
     *
     * @return EmailTemplates
     */
    public static function getByName(string $name) : EmailTemplates
    {
        $di = Di::getDefault();
        $appId = $di->getApp()->getId();

        $companyId = $di->has('userData') ? $di->getUserData()->currentCompanyId() : 0;

        $emailTemplate = self::findFirst([
            'conditions' => 'companies_id in (?0, 0) and apps_id in (?1, 0) and name = ?2 and is_deleted = 0',
            'bind' => [$companyId, $appId, $name],
            'order' => 'id desc'
        ]);

        if (!is_object($emailTemplate)) {
            throw new UnprocessableEntityException(_('No template ' . $name . ' found for this app ' . Di::getDefault()->getApp()->name));
        }

        //@todo add company id
        $emailTemplate->name .= '-' . $appId;
        return $emailTemplate;
    }
}
