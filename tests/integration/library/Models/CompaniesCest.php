<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\CustomFields\CustomFields;
use Canvas\Models\Apps;
use Canvas\Models\Companies;
use Canvas\Models\CompaniesAssociations;
use Canvas\Models\CompaniesBranches;
use Canvas\Models\CompaniesCustomFields;
use Canvas\Models\CompaniesGroups;
use Canvas\Models\CompaniesSettings;
use Canvas\Models\FileSystemEntities;
use Canvas\Models\Subscription;
use Canvas\Models\UserCompanyApps;
use Canvas\Models\Users;
use Canvas\Models\UsersAssociatedApps;
use Canvas\Models\UsersAssociatedCompanies;
use Canvas\Models\UserWebhooks;
use IntegrationTester;
use Phalcon\Security\Random;

class CompaniesCest
{
    public function validateRelationships(IntegrationTester $I)
    {
        $actual = $I->getModelRelationships(Companies::class);

        $expected = [
            [0, 'users_id', Users::class, 'id', ['alias' => 'user']],
            [2, 'id', CompaniesSettings::class, 'id', ['alias' => 'settings']],
            [2, 'id', CompaniesBranches::class, 'companies_id', ['alias' => 'branches']],
            [2, 'id', CompaniesCustomFields::class, 'companies_id', ['alias' => 'fields']],
            [2, 'id', CustomFields::class, 'companies_id', ['alias' => 'custom-fields']],
            [2, 'id', UsersAssociatedCompanies::class, 'companies_id', ['alias' => 'UsersAssociatedCompanies']],
            [2, 'id', UsersAssociatedApps::class, 'companies_id', ['alias' => 'UsersAssociatedApps']],
            [2, 'id', UsersAssociatedApps::class, 'companies_id', ['alias' => 'UsersAssociatedByApps', 'params' => ['conditions' => 'apps_id = 1']]],
            [2, 'id', CompaniesAssociations::class, 'companies_id', ['alias' => 'companiesAssoc']],
            [2, 'id', Subscription::class, 'companies_id', ['alias' => 'subscriptions', 'params' => ['conditions' => 'apps_id = 1 AND is_deleted = 0', 'order' => 'id DESC']]],
            [2, 'id', UserWebhooks::class, 'companies_id', ['alias' => 'user-webhooks']],
            [1, 'id', CompaniesBranches::class, 'companies_id', ['alias' => 'defaultBranch', 'params' => ['conditions' => 'is_default = 1']]],
            [1, 'id', CompaniesBranches::class, 'companies_id', ['alias' => 'branch']],
            [1, 'id', UserCompanyApps::class, 'companies_id', ['alias' => 'app', 'params' => ['conditions' => 'apps_id = 1']]],
            [1, 'id', UserCompanyApps::class, 'companies_id', ['alias' => 'apps', 'params' => ['conditions' => 'apps_id = 1']]],
            [1, 'id', Subscription::class, 'companies_id', ['alias' => 'subscription', 'params' => ['conditions' => 'apps_id = 1 AND is_deleted = 0', 'order' => 'id DESC']]],
            [1, 'id', FileSystemEntities::class, 'entity_id', ['alias' => 'files', 'params' => ['conditions' => 'system_modules_id = ?0', 'bind' => [1]]]],
            [4, 'id', Users::class, 'id', ['alias' => 'users', 'params' => ['conditions' => 'apps_id = 1 AND Canvas\Models\UsersAssociatedApps.is_deleted = 0']]],
        ];

        $I->assertEquals($expected, $actual);
    }

    /**
     * Get the Companies Group to which a newly created company belongs to.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getCompaniesGroupsTest(IntegrationTester $I)
    {
        $app = Apps::getACLApp(Apps::CANVAS_DEFAULT_APP_NAME);
        $companyGroup = CompaniesGroups::findFirst([
            'conditions' => 'users_id = ?0 and apps_id = ?1 and is_deleted = 0',
            'bind' => [$I->grabFromDi('userData')->id, $app->id]
        ]);
        $I->assertTrue($companyGroup instanceof CompaniesGroups);
    }

    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     *
     * @return void
     */
    public function getCompaniesAssociationsTest(IntegrationTester $I)
    {
        $company = Companies::getDefaultByUser($I->grabFromDi('userData'));
        $companyAssociations = CompaniesAssociations::findFirst([
            'conditions' => 'companies_id = ?0 and is_deleted = 0',
            'bind' => [$company->id]
        ]);
        $I->assertTrue($companyAssociations instanceof CompaniesAssociations);
    }

    /**
     * Register a new Company.
     *
     * @param IntegrationTester $I
     *
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
     *
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
     *
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
