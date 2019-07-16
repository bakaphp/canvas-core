<?php

namespace Canvas\Tests\unit;

use Canvas\Providers\DatabaseProvider;
use Phalcon\Di\FactoryDefault;
use UnitTester;
use Canvas\Models\Users;
use Canvas\Models\Companies;
use Canvas\Providers\AppProvider;
use Canvas\Providers\ConfigProvider;
use Phalcon\Security\Random;

class CompaniesCest
{
    private $diContainer;

    public function setDatabaseProvider(UnitTester $I){
        
        $this->diContainer = new FactoryDefault();

        $configProvider = new ConfigProvider();
        $configProvider->register($this->diContainer);

        $databaseProvider = new DatabaseProvider();
        $databaseProvider->register($this->diContainer);

        $appProvider = new AppProvider();
        $appProvider->register($this->diContainer);

        $user = Users::findFirst(1);
        $I->assertTrue($user instanceof Users);

        $this->diContainer->setShared('userData', $user);

        $I->assertTrue($this->diContainer->has('config'));
        $I->assertTrue($this->diContainer->has('app'));
        $I->assertTrue($this->diContainer->has('db'));
        $I->assertTrue($this->diContainer->has('userData'));
    }
    /**
     * Register a new Company
     *
     * @param UnitTester $I
     * @return void
     */
    public function registerTest(UnitTester $I)
    {
        $random = new Random();
        $newCompany = Companies::register( $this->diContainer->get('userData'), 'TestCompany-'. $random->base58());
        $I->assertTrue($newCompany instanceof Companies);
    }

    /**
     * Register a new Company
     *
     * @param UnitTester $I
     * @return void
     */
    public function getDefaultByUserTest(UnitTester $I)
    {
        $company = Companies::getDefaultByUser($this->diContainer->get('userData'));
        $I->assertTrue($company instanceof Companies);
    }

    /**
     * Get Associated Users by App
     *
     * @param UnitTester $I
     * @return void
     */
    public function getAssociatedUsersByAppTest(UnitTester $I)
    {
        $random = new Random();
        $newCompany = Companies::register( $this->diContainer->get('userData'), 'TestCompany-'. $random->base58());
        $I->assertTrue($newCompany instanceof Companies);

        $userInfo = $newCompany->getAssociatedUsersByApp()[0];
        $I->assertTrue(gettype($userInfo) == 'string');
    }

    /**
     * Get Logo
     *
     * @param UnitTester $I
     * @return void
     */
    public function getLogoTest(UnitTester $I)
    {
        $company = Companies::getDefaultByUser($this->diContainer->get('userData'));
        $I->assertTrue($company instanceof Companies);

        $logo = $company->getLogo();
        $I->assertTrue(gettype($logo) == 'object');
    }
}
