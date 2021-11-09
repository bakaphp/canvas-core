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

        //if users is less than 10, create 10 users
        // if ($users->count() < 10) {
        //     for ($i = 0; $i < 10; $i++) {
        //         $user = new Users();
        //         $user->email = 'test' . $i . '@test.com';
        //         $user->password = 'test';
        //         $user->save();
        //     }
        // }

        foreach($users as $userGroup) {
            for ($i = 0; $i < 10; $i++) {
                $user->notify(new NewFollower($userGroup, true));
            }
        }

        $notifications = Notifications::findFirst([
            'order' => 'updated_at DESC'
        ]);
        
        $I->assertJson($notifications->group, 'is a valid json');
        $groupUsers = json_decode($notifications->group);
        $I->assertEquals(count($groupUsers->from_users), 2);
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
