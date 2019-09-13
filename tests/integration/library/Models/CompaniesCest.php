<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Companies;
use IntegrationTester;
use Canvas\Providers\ConfigProvider;
use Phalcon\Di\FactoryDefault;
use Phalcon\Security\Random;

class CompaniesCest
{
    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function registerTest(IntegrationTester $I)
    {
        $random = new Random();
        $newCompany = Companies::register($I->grabFromDi('userData'), 'TestCompany-' . $random->base58());
        $I->assertTrue($newCompany instanceof Companies);
    }

    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getDefaultByUserTest(IntegrationTester $I)
    {
        $company = Companies::getDefaultByUser($I->grabFromDi('userData'));
        $I->assertTrue($company instanceof Companies);
    }

    /**
     * Get Associated Users by App.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getAssociatedUsersByAppTest(IntegrationTester $I)
    {
        $random = new Random();
        $newCompany = Companies::register($I->grabFromDi('userData'), 'TestCompany-' . $random->base58());
        $I->assertTrue($newCompany instanceof Companies);

        $userInfo = $newCompany->getAssociatedUsersByApp()[0];
        $I->assertTrue(gettype($userInfo) == 'string');
    }

    /**
     * Get Logo.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getLogoTest(IntegrationTester $I)
    {
        $company = Companies::getDefaultByUser($I->grabFromDi('userData'));
        $I->assertTrue($company instanceof Companies);

        $logo = $company->getLogo();
        $I->assertTrue(gettype($logo) == 'object');
    }
}
