<?php

namespace Canvas\Tests\unit\library\Providers;

use Canvas\Providers\ConfigProvider;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault;
use UnitTester;

class ConfigCest
{
    /**
     * @param UnitTester $I
     */
    public function checkRegistration(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($diContainer);

        $I->assertTrue($diContainer->has('config'));
        $config = $diContainer->getShared('config');
        $I->assertTrue($config instanceof Config);

        $configArray = $config->toArray();

        //App
        $I->assertTrue(isset($configArray['app']['version']));
        $I->assertTrue(isset($configArray['app']['timezone']));
        $I->assertTrue(isset($configArray['app']['debug']));
        $I->assertTrue(isset($configArray['app']['env']));
        $I->assertTrue(isset($configArray['app']['devMode']));
        $I->assertTrue(isset($configArray['app']['baseUri']));
        $I->assertTrue(isset($configArray['app']['supportEmail']));
        $I->assertTrue(isset($configArray['app']['time']));
        $I->assertTrue(isset($configArray['app']['namespaceName']));
        $I->assertTrue(isset($configArray['app']['subscription']['defaultPlan']['name']));

        //Application
        $I->assertTrue(isset($configArray['application']['production']));
        $I->assertTrue(isset($configArray['application']['development']));
        $I->assertTrue(isset($configArray['application']['jwtSecurity']));
        $I->assertTrue(isset($configArray['application']['debug']['profile']));
        $I->assertTrue(isset($configArray['application']['debug']['logQueries']));
        $I->assertTrue(isset($configArray['application']['debug']['logRequest']));

        //FileSystem
        $I->assertTrue(isset($configArray['filesystem']['uploadDirectoy']));
        $I->assertTrue(isset($configArray['filesystem']['local']['path']));
        $I->assertTrue(isset($configArray['filesystem']['local']['cdn']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['info']['credentials']['key']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['info']['credentials']['secret']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['info']['region']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['info']['version']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['path']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['bucket']));
        $I->assertTrue(isset($configArray['filesystem']['s3']['cdn']));

        //Cache
        $I->assertTrue(isset($configArray['cache']));
        $I->assertTrue(isset($configArray['cache']['data']));
        $I->assertTrue(isset($configArray['cache']['data']['front']));
        $I->assertTrue(isset($configArray['cache']['data']['back']));
        $I->assertTrue(isset($configArray['cache']['metadata']));
        $I->assertTrue(isset($configArray['cache']['metadata']['dev']));
        $I->assertTrue(isset($configArray['cache']['metadata']['prod']));

        //Email
        $I->assertTrue(isset($configArray['email']));
        $I->assertTrue(isset($configArray['email']['driver']));
        $I->assertTrue(isset($configArray['email']['host']));
        $I->assertTrue(isset($configArray['email']['port']));
        $I->assertTrue(isset($configArray['email']['username']));
        $I->assertTrue(isset($configArray['email']['password']));
        $I->assertTrue(isset($configArray['email']['from']));
        $I->assertTrue(isset($configArray['email']['from']['email']));
        $I->assertTrue(isset($configArray['email']['from']['name']));
        $I->assertTrue(isset($configArray['email']['debug']['from']['email']));
        $I->assertTrue(isset($configArray['email']['debug']['from']['name']));

        //Beanstalk
        $I->assertTrue(isset($configArray['beanstalk']));
        $I->assertTrue(isset($configArray['beanstalk']['host']));
        $I->assertTrue(isset($configArray['beanstalk']['port']));
        $I->assertTrue(isset($configArray['beanstalk']['prefix']));

        //ElasticSearch
        $I->assertTrue(isset($configArray['elasticSearch']));
        $I->assertTrue(isset($configArray['elasticSearch']['hosts']));

        //JWT
        $I->assertTrue(isset($configArray['jwt']));
        $I->assertTrue(isset($configArray['jwt']['secretKey']));
        $I->assertTrue(isset($configArray['jwt']['payload']['exp']));
        $I->assertTrue(isset($configArray['jwt']['payload']['iss']));

        //Pusher
        $I->assertTrue(isset($configArray['pusher']));
        $I->assertTrue(isset($configArray['pusher']['id']));
        $I->assertTrue(isset($configArray['pusher']['key']));
        $I->assertTrue(isset($configArray['pusher']['secret']));
        $I->assertTrue(isset($configArray['pusher']['cluster']));
        $I->assertTrue(isset($configArray['pusher']['queue']));

        //Stripe
        $I->assertTrue(isset($configArray['stripe']));
        // $I->assertTrue(isset($configArray['stripe']['secret']));
        // $I->assertTrue(isset($configArray['stripe']['public']));

        //Throttle
        $I->assertTrue(isset($configArray['throttle']));
        $I->assertTrue(isset($configArray['throttle']['bucketSize']));
        $I->assertTrue(isset($configArray['throttle']['refillTime']));
        $I->assertTrue(isset($configArray['throttle']['refillAmount']));
    }
}
