<?php
declare(strict_types=1);

namespace Canvas\Models;

use Baka\Database\Exception\ModelNotFoundException;
use Canvas\Contracts\SubscriptionPlanLimitTrait;
use Phalcon\Di;
use Baka\Support\Random;

class UsersInvite extends AbstractModel
{
    use SubscriptionPlanLimitTrait;

    public string $invite_hash;
    public int $users_id;
    public int $companies_id;
    public int $role_id;
    public int $apps_id;
    public string $email;

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
     * Get the invite by hash.
     *
     * @param string $hash
     *
     * @return UsersInvite
     */
    public static function getByHash(string $hash) : UsersInvite
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
     *
     * @param string $email
     *
     * @return bool
     */
    public static function isValid(string $email, int $roleId = 1) : bool
    {
        $userData = Di::getDefault()->get('userData');
        $app = Di::getDefault()->get('app');

        //check inviste
        $invitedUser = self::findFirst([
            'conditions' => 'email = ?0 and companies_id = ?1 and role_id = ?2 and apps_id = ?3 and is_deleted = 0',
            'bind' => [$email, $userData->currentCompanyId(), $roleId, $app->getId()]
        ]);

        if (is_object($invitedUser)) {
            throw new ModelNotFoundException('User already invited to this company app');
        }

        //check for user if they already are in this company app
        $userExists = Users::findFirst([
            'conditions' => 'email = ?0 and is_deleted = 0',
            'bind' => [$email]
        ]);

        if (is_object($userExists)) {
            $softDeleteCheck = UsersAssociatedApps::findFirst([
                'conditions' => 'users_id = ?0 and companies_id = ?1 and is_deleted = 0',
                'bind' => [$userExists->id, $userData->currentCompanyId()]
            ]);
            if ($userData->defaultCompany->userAssociatedToCompany($userExists) && is_object($softDeleteCheck)) {
                throw new ModelNotFoundException('User already is associated with this company app');
            }
        }

        return true;
    }

    /**
     * Given the form request return a new user invite.
     *
     * @param array $request
     *
     * @return Users
     */
    public function newUser(array $request) : Users
    {
        $user = new Users();
        $user->firstname = $request['firstname'];
        $user->lastname = $request['lastname'];
        $user->password = $request['password'];
        $user->email = $this->email;
        $user->displayname = $request['displayname'] ?? Random::generateDisplayName($user->email);
        $user->user_active = 1;
        $user->roles_id = $this->role_id;
        $user->created_at = date('Y-m-d H:m:s');
        $user->default_company = $this->companies_id;
        $user->default_company_branch = $this->company->branch->getId();

        return $user;
    }
}
