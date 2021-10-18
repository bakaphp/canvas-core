<?php

namespace Canvas\Tests\integration\library\Models\Notifications;

use Canvas\Models\Notifications\UserSettings;
use Canvas\Models\NotificationType;
use IntegrationTester;

class UserSettingsCest
{
    public function isEnabled(IntegrationTester $I)
    {
        $notificationType = NotificationType::findFirst();
        $isEnabled = UserSettings::isEnabled($I->grabFromDi('app'), $I->grabFromDi('userData'), $notificationType);

        $I->assertIsBool($isEnabled);
    }

    public function getByUserAndNotificationType(IntegrationTester $I)
    {
        $notificationType = NotificationType::findFirst();
        $userSetting = UserSettings::getByUserAndNotificationType($I->grabFromDi('app'), $I->grabFromDi('userData'), $notificationType);

        $I->assertTrue($userSetting === null || $userSetting instanceof UserSettings);
    }

    public function muteAll(IntegrationTester $I)
    {
        $UserSettings = new UserSettings();
        $muteAll = $UserSettings->muteAll($I->grabFromDi('app'), $I->grabFromDi('userData'));

        $I->assertIsBool($muteAll);
    }
}
