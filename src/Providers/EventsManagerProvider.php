<?php

namespace Canvas\Providers;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Canvas\Listener\Subscription;
use Canvas\EventsManager;
use Canvas\Listener\User;

class EventsManagerProvider implements ServiceProviderInterface
{
    /**
     * List of the listeners use by the app.
     *
     * [
     *  'eventName' => 'className'
     * ];
     *
     * @var array
     */
    protected $listeners = [
    ];

    protected $canvasListeners = [
        'subscription' => Subscription::class,
        'user' => User::class,
    ];

    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'events',
            function () {
                $eventsManager = new EventsManager();

                return $eventsManager;
            }
        );

        $this->attachEvents($container, $this->listeners);
        $this->attachEvents($container, $this->canvasListeners);
    }

    /**
     * given the DI attach the list of containers.
     *
     * @param DiInterface $container
     * @param array $listeners
     * @return void
     */
    protected function attachEvents(DiInterface $container, array $listeners): bool
    {
        if (empty($listeners)) {
            return false;
        }
        $eventsManager = $container->get('eventsManager');

        //navigate the list of listener and create the events
        foreach ($listeners as $key => $listen) {
            //create the events given the key
            $eventsManager->attach(
                $key,
                new $listen()
            );
        }

        return true;
    }
}
