<?php

namespace Canvas\Tests\integration\library\Jobs;

use Baka\Notifications\Notify;
use Canvas\Models\Notifications;
use Canvas\Models\Users;
use Canvas\Notifications\PasswordUpdate;
use Canvas\Tests\Support\Notifications\NewFollower;
use IntegrationTester;
use Page\Data;

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
        for ($i = 0; $i < 20; $i++) {
            $email = !Users::findFirstByEmail(Data::$defaultEmail) ? Data::$defaultEmail : $I->faker()->email;

            $I->sendPOST(Data::$usersUrl, [
                'email' => $email,
                'password' => Data::$defaultPassword,
                'verify_password' => Data::$defaultPassword,
                'firstname' => $I->faker()->firstName,
                'lastname' => $I->faker()->lastName,
                'displayname' => $I->faker()->userName,
                'default_company' => $I->faker()->domainWord,
            ]);
        }

        $users = Users::find();
        $user = Users::findFirstById(1);

        foreach ($users as $userGroup) {
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
