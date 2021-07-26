<?php

namespace Canvas\Tests\api;

use ApiTester;
use Canvas\Models\AppsPlans;
use Canvas\Models\Companies;
use Canvas\Models\UserCompanyAppsActivities;
use Canvas\Models\Users;
use Exception;
use Page\Data;
use Phalcon\Di;
use Phalcon\Security\Random;

class SubscriptionLimitCest
{
    /**
     * Confirm working with a system model update its total activity for the app and company the
     * users is working with.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function updateActivity(ApiTester $I) : void
    {
        //we are going to test the activity of 1 modele (users)

        //first we need to invite a new user to the current company
        $userData = $I->apiLogin();
        $random = new Random();
        $userName = $random->base58();

        $testEmail = $userName . '@example.com';

        $I->haveHttpHeader('Authorization', $userData->token);

        //set di
        Di::getDefault()->set(
            'userData',
            function () {
                return Users::findFirstByEmail(Data::loginJson()['email']);
            }
        );

        //set limit to 10 so we can fail
        $appPlanSettings = AppsPlans::findFirst(1)->set('users_total', 10);

        $user = Users::findFirst($userData->id);
        $company = Companies::findFirstByUsers_id($userData->id);
        $userActivity = new UserCompanyAppsActivities();
        $userActivity->companies_id = $company->getId();
        $userActivity->company_branches_id = $user->currentBranchId();
        $userActivity->apps_id = 1; //default first app
        $userActivity->key = 'users_total'; //default first app
        $userActivity->value = 0; //default first app
        $userActivity->save();

        //get current total user activity
        $preTotalUserActivities = UserCompanyAppsActivities::findFirst([
            'conditions' => 'companies_id = ?0 and key = ?1',
            'bind' => [$company->getId(), 'users_total']
        ]);

        $I->sendPost('/v1/users/invite', [
            'email' => $testEmail,
            'role_id' => 1,
            'dont_send' => 1
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['email'] == $testEmail);

        $hash = $data['invite_hash'];

        $I->sendPost('/v1/users-invite/' . $hash, [
            'firstname' => 'testFirstsName',
            'lastname' => 'testLastName',
            'displayname' => $userName,
            'password' => 'testpassword',
            'user_active' => 1
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $dataInvite = json_decode($response, true);

        $totalUserActivities = UserCompanyAppsActivities::findFirst([
            'conditions' => 'companies_id = ?0 and key = ?1',
            'bind' => [$company->getId(), 'users_total']
        ]);

        //now after inviting a new user the total users for this app company should have increased
        $I->assertTrue($totalUserActivities->value > $preTotalUserActivities->value);
    }

    /**
     * Confirm by chaging the total usage of the plan for the test account, we encounter the limit exception.
     *
     * @param ApiTester $I
     *
     * @return void
     */
    public function isAtLimit(ApiTester $I) : void
    {
        //we are going to test the activity of 1 modele (users)

        //first we need to invite a new user to the current company
        $userData = $I->apiLogin();
        $random = new Random();
        $userName = $random->base58();

        $testEmail = $userName . '@example.com';

        $I->haveHttpHeader('Authorization', $userData->token);

        //set limit to 1 so we can fail
        $appPlanSettings = AppsPlans::findFirst(1)->set('users_total', 1);

        $I->sendPost('/v1/users/invite', [
            'email' => $testEmail,
            'role_id' => 1,
            'dont_send' => 1
        ]);

        $I->seeResponseIsSuccessful();
        $response = $I->grabResponse();
        $data = json_decode($response, true);

        $I->assertTrue($data['email'] == $testEmail);

        $hash = $data['invite_hash'];
        $reachLimit = false;

        try {
            $I->sendPost('/v1/user-invites/' . $hash, [
                'firstname' => 'testFirstsName',
                'lastname' => 'testLastName',
                'displayname' => $userName,
                'password' => 'testpassword',
                'user_active' => 1
            ]);

            $I->seeResponseIsSuccessful();
            $response = $I->grabResponse();
            $dataInvite = json_decode($response, true);
        } catch (Exception $e) {
            $reachLimit = true;

            //are we at our limit?
            $I->assertTrue($reachLimit);
        }
    }
}
