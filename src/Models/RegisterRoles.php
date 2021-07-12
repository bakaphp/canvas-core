<?php
declare(strict_types=1);

namespace Canvas\Models;

use Phalcon\Di;
use Baka\Validation as CanvasValidation;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Security\Random;

class RegisterRoles extends AbstractModel
{
    public string $uuid;
    public int $apps_id;
    public int $companies_id;
    public int $roles_id;

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
            'roles_id',
            'Canvas\Models\Roles',
            'id',
            ['alias' => 'role']
        );

        $this->setSource('register_roles');
    }

    /**
    * Before create system modules forms
    *
    * @return void
    */
    public function beforeCreate()
    {
        $random = new Random();

        $this->uuid = $random->uuid();
        $this->companies_id = Di::getDefault()->getUserData()->currentCompanyId();
        $this->apps_id = Di::getDefault()->getApp()->getId();
        parent::beforeCreate();
    }

    /**
     * Get register role by uuid
     *
     * @param string $uuid
     *
     * return
     */
    public static function getByUuid(string $uuid): self
    {
        return self::findFirstOrFail([
            "conditions" => "uuid = :uuid: and is_deleted = 0",
            "bind" => ["uuid" => $uuid]
        ]);
    }
}
