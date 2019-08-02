<?php
declare(strict_types=1);

namespace Canvas\Models;

use Canvas\Traits\SubscriptionPlanLimitTrait;
use Canvas\Exception\ModelException;
use Phalcon\Di;

class UsersInvite extends AbstractModel
{
    use SubscriptionPlanLimitTrait;

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $invite_hash;

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
     * @var integer
     */
    public $role_id;

    /**
     *
     * @var integer
     */
    public $app_id;

    /**
     *
     * @var string
     */
    public $email;

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
     * Subscription plan key.
     */
    protected $subscriptionPlanLimitKey = 'users';

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource('users_invite');

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
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource(): string
    {
        return 'users_invite';
    }

    /**
     * What to do after the creation of a new users
     *  - Assign default role.
     *
     * @return void
     */
    public function afterCreate()
    {
        $this->updateAppActivityLimit();
    }

    /**
     * Get the invite by hash
     *
     * @param string $hash
     * @return UsersInvite
     */
    public static function getByHash(string $hash): UsersInvite
    {
        return self::findFirstOrFail([
            'conditions' => 'invite_hash = ?0 and is_deleted = 0',
            'bind' => [$hash]
        ]);
    }

    /**
     * Validate if the current email is valid to invite.
     *
     * @throws Exception
     * @param string $email
     * @return bool
     */
    public static function isValid(string $email, int $roleId = 1): bool
    {
        $userData = Di::getDefault()->getUserData();
        //check inviste
        $invitedUser = self::findFirst([
            'conditions' => 'email = ?0 and companies_id = ?1 and role_id = ?2 and is_deleted = 0',
            'bind' => [$email, $userData->currentCompanyId(), $roleId]
        ]);

        if (is_object($invitedUser)) {
            throw new ModelException('User already invited to this company app');
        }

        //check for user if they already are in this company app
        $userExists = Users::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0',
            'bind' => [$email]
        ]);

        if (is_object($userExists)) {
            if ($userData->defaultCompany->userAssociatedToCompany($userExists)) {
                throw new ModelException('User already is associated with this company app');
            }
        }

        return true;
    }

    /**
     * Given the form request return a new user invite.
     *
     * @param array $requets
     * @return void
     */
    public function newUser(array $request): Users
    {
        $user = new Users();
        $user->firstname = $request['firstname'];
        $user->lastname = $request['lastname'];
        $user->displayname = $request['displayname'];
        $user->password = $request['password'];
        $user->email = $this->email;
        $user->user_active = 1;
        $user->roles_id = $this->role_id;
        $user->created_at = date('Y-m-d H:m:s');
        $user->default_company = $this->companies_id;
        $user->default_company_branch = $this->company->branch->getId();

        return $user;
    }
}
