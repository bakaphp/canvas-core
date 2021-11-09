<?php

namespace Canvas\Tests\integration\library\Jobs;

use Baka\Notifications\Notify;
use Canvas\Models\Notifications;
use Canvas\Models\Users;
use Canvas\Notifications\PasswordUpdate;
use Canvas\Tests\Support\Notifications\NewFollower;
use IntegrationTester;

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

    public function grupedNotifications(IntegrationTester $I)
    {
        $users = Users::find();
        $user = Users::findFirstById(1);

        //if users is below 10, create 15 users
        if ($users->count() < 10) {
            for ($i = 0; $i < 15; $i++) {
                $user = new Users();
                $user->uuid = "uuid" . $i;
                $user->firstname = "firstname" . $i;
                $user->lastname = "lastname" . $i;
                $user->displayname = "displayname" . $i;
                $user->email = "email" . $i;
                $user->default_company = 1;
                $user->default_company_branch = 1;
                $user->system_modules_id = 1;
                $user->user_active = 1;
                $user->password = password_hash('password', PASSWORD_DEFAULT);
                $user->created_at = date('Y-m-d H:i:s');
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();
            }
        }


        foreach($users as $userGroup) {
            for ($i = 0; $i <= 10; $i++) {
                $user->notify(new NewFollower($userGroup, true));
            }
        }

        $notifications = Notifications::findFirst([
            'order' => 'updated_at DESC'
        ]);

        $users = Users::find();
        $I->assertEquals($users->count(), 2);

        $I->assertJson($notifications->group, 'is a valid json');
        $groupUsers = json_decode($notifications->group);
        $I->assertEquals(count($groupUsers->from_users), 11);
        $I->assertIsArray($groupUsers->from_users, 'has a group');
    }

    public function nonGroupedNotifications(IntegrationTester $I)
    {
        $users = Users::find('id in (1, 2)');
        $user = Users::findFirst();
        $user->notify(new NewFollower($users->getFirst()));

        $notifications = Notifications::findFirst([
            'order' => 'created_at DESC'
        ]);

        $I->assertNull($notifications->group, 'is not grouped');

    }

    
}
