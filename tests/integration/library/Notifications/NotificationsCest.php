<?php

namespace Canvas\Tests\integration\library\Jobs;

use Baka\Notifications\Notify;
use Canvas\Models\Companies;
use Canvas\Models\Notifications;
use Canvas\Models\Users;
use Canvas\Notifications\PasswordUpdate;
use Canvas\Tests\Support\Notifications\NewComment;
use Canvas\Tests\Support\Notifications\NewFollower;
use IntegrationTester;
use Phalcon\Di;

class NotificationsCest
{
    public function notifyOne(IntegrationTester $I)
    {
        $user = Users::findFirst();
        $user->notify(new PasswordUpdate($user));
    }

    public function notifyAll(IntegrationTester $I)
    {
        $users = Users::find('id in (1, 2)');
        $user = Users::findFirst();
        Notify::all($users, new PasswordUpdate($user));
    }

    public function globalCounter(IntegrationTester $I)
    {
        $users = Users::find('id in (1, 2)');
        $user = Users::findFirst();
        $user->notify(new NewFollower($users->getFirst()));
    }

    public function groupedNotifications(IntegrationTester $I)
    {
        //if users is below 10, create 15 users
        if (Users::count() < 10) {
            for ($i = 0; $i < 20; $i++) {
                $user = new Users();
                $user->firstname = 'firstname' . $i;
                $user->lastname = 'lastname' . $i;
                $user->displayname = 'displayname' . $i;
                $user->email = 'email' . $i . '@domain.com';
                $user->default_company = 1;
                $user->default_company_branch = 1;
                $user->system_modules_id = 1;
                $user->user_active = 1;
                $user->password = password_hash('password', PASSWORD_DEFAULT);
                $user->created_at = date('Y-m-d H:i:s');
                $user->updated_at = date('Y-m-d H:i:s');
                $user->saveOrFail();
            }
        }

        $users = Users::find([
            'condition' => 'id != 1 ',
            'limit' => 10
        ]);

        $user = Users::findFirst(1);

        foreach ($users as $userGroup) {
            Di::getDefault()->set('userData', $userGroup);
            $user->notify(new NewFollower($userGroup, true));
        }

        $notifications = Notifications::findFirst([
            'order' => 'id DESC'
        ]);

        Di::getDefault()->set('userData', $user);

        print_r($notifications->toArray());
        die();

        $I->assertJson($notifications->content_group, 'is a valid json');
        $groupUsers = json_decode($notifications->content_group);

        //total notification + the original creator
        $I->assertEquals(count($groupUsers->from_users), $users->count());
        $I->assertIsArray($groupUsers->from_users, 'has a group');
    }

    public function groupByEntity(IntegrationTester $I)
    {
        $users = Users::find([
            'condition' => 'id != 1 ',
            'limit' => 10
        ]);

        $user = Users::findFirst(1);
        $company = Companies::findFirst();
        foreach ($users as $userGroup) {
            Di::getDefault()->set('userData', $userGroup);
            $user->notify(new NewComment($company, true));
        }

        $notifications = Notifications::findFirst([
            'order' => 'id DESC'
        ]);

        Di::getDefault()->set('userData', $user);

        $I->assertJson($notifications->content_group, 'is a valid json');
        $groupUsers = json_decode($notifications->content_group, true);

        //total notification + the original creator
        $I->assertEquals($groupUsers['total'], $users->count());
        $I->assertIsArray($groupUsers, 'has a group');
    }

    public function nonGroupedNotifications(IntegrationTester $I)
    {
        $users = Users::find('id in (1, 2)');
        $user = Users::findFirst();
        Di::getDefault()->set('userData', $users->getFirst());
        $user->notify(new NewFollower($users->getFirst()));

        $notifications = Notifications::findFirst([
            'order' => 'created_at DESC'
        ]);

        $I->assertNull($notifications->group, 'is not grouped');
    }
}
