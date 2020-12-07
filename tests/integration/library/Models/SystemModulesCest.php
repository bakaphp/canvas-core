<?php

namespace Gewaer\Tests\integration\library\Models;

use Canvas\Models\SystemModules;
use IntegrationTester;

class SystemModulesCest
{
    /**
     * Get System Module By Model Name Users.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getSystemModuleByModelNameUser(IntegrationTester $I)
    {
        $systemModule = SystemModules::getByModelName('Canvas\Models\Users');
        $I->assertTrue($systemModule instanceof SystemModules);
    }

    /**
     * Get System Module By Model Name Companies.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getSystemModuleByModelNameCompanies(IntegrationTester $I)
    {
        $systemModule = SystemModules::getByModelName('Canvas\Models\Companies');
        $I->assertTrue($systemModule instanceof SystemModules);
    }

    public function getByModelName(IntegrationTester $I)
    {
        $systemModule = SystemModules::getByModelName('Canvas\Models\Companies');
        $I->assertTrue($systemModule instanceof SystemModules);
    }

    public function getByName(IntegrationTester $I)
    {
        $systemModule = SystemModules::getByName('Companies');
        $I->assertTrue($systemModule instanceof SystemModules);
    }

    public function getById(IntegrationTester $I)
    {
        $systemModule = SystemModules::getById(1);
        $I->assertTrue($systemModule instanceof SystemModules);
    }

    /**
     * Get System Module by slug.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function getBySlug(IntegrationTester $I)
    {
        $systemModule = SystemModules::getBySlug('users');
        $I->assertTrue($systemModule instanceof SystemModules);
    }

    /**
     * Given tell them if this system module is index in elastic.
     *
     * @param IntegrationTester $I
     * @return void
     */
    public function useElastic(IntegrationTester $I)
    {
        $systemModule = SystemModules::getBySlug('users');
        $I->assertTrue(gettype($systemModule->useElastic()) == 'boolean');
    }
}
