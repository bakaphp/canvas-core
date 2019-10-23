<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\Companies;
use Canvas\Models\Apps;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\CompaniesAssociations;
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
     * Get the Companies Group to which a newly created company belongs to.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getCompaniesGroupsTest(IntegrationTester $I)
    {
        $app = Apps::getACLApp(Apps::CANVAS_DEFAULT_APP_NAME);
        $companyGroup = CompaniesGroups::findFirst([
            'conditions'=>'users_id = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind'=>[$I->grabFromDi('userData')->id,$app->id]
        ]);
        $I->assertTrue($companyGroup instanceof CompaniesGroups);
    }

    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getCompaniesAssociationsTest(IntegrationTester $I)
    {
        $company = Companies::getDefaultByUser($I->grabFromDi('userData'));
        $companyAssociations = CompaniesAssociations::findFirst([
            'conditions'=>'companies_id = ?0 and is_deleted = 0',
            'bind'=>[$company->id]
        ]);
        $I->assertTrue($companyAssociations instanceof CompaniesAssociations);
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
