<?php

namespace Canvas\Tests\unit\library\Dto;

use Canvas\Hashing\Password;
use UnitTester;

class PasswordCest
{
    public function createPassword(UnitTester $I)
    {
        $password = Password::make('somethingsomething');
        $I->assertTrue(strlen($password) >= 12);
    }

    public function passwordHashMatch(UnitTester $I)
    {
        $password = 'somethingsomething';
        $passwordHash = Password::make($password);

        $I->assertTrue(Password::check($password, $passwordHash));
    }

    public function passwordHashDontMatch(UnitTester $I)
    {
        $password = 'somethingsomething';
        $passwordHash = Password::make($password);

        $I->assertFalse(Password::check($password.'extra', $passwordHash));
    }

    public function doesntNeedsRehash(UnitTester $I)
    {
        $password = 'somethingsomething';
        $passwordHash = Password::make($password);

        $I->assertFalse(Password::needsRehash($passwordHash));
    }
}
