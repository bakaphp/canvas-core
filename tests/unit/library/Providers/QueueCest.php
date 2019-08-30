<?php

namespace Canvas\Tests\unit\library\Providers;

use Canvas\Providers\QueueProvider;
use Canvas\Providers\ConfigProvider;
use Canvas\Providers\DatabaseProvider;
use Phalcon\Di\FactoryDefault;
use UnitTester;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class QueueCest
{
    /**
     * @param UnitTester $I
     */
    public function checkRegistration(UnitTester $I)
    {
        $diContainer = new FactoryDefault();
        $provider = new ConfigProvider();
        $provider->register($diContainer);
        $provider = new DatabaseProvider();
        $provider->register($diContainer);
        $provider = new QueueProvider();
        $provider->register($diContainer);

        $I->assertTrue($diContainer->has('queue'));

        $queue = $diContainer->getShared('queue');
        $I->assertTrue($queue instanceof AMQPStreamConnection);
    }
}
