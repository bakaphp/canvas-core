<?php

declare(strict_types=1);

namespace Canvas\Providers;

use function Baka\appPath;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\View\Simple as SimpleView;

class ViewProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container) : void
    {
        $config = $container->get('config');

        /**
         * Setting up the view component.
         */
        $container->set('view', function () use ($config, $container) {
            $view = new SimpleView();
            $view->setViewsDir($config->filesystem->local->path . '/view/');
            $view->registerEngines([
                '.volt' => function ($view) use ($config, $container) {
                    $volt = new VoltEngine($view, $container);
                    $volt->setOptions([
                        //CACHE save DISABLED IN DEV ENVIRONMENT
                        'path ' => appPath('storage/cache/volt/'),
                        'separator ' => '_',
                        'always ' => !$config->app->production,
                    ]);

                    $volt->getCompiler()->addExtension(new class {
                        /**
                         * This method is called for any PHP function on the volt.
                         */
                        public function compileFunction($name, $arguments)
                        {
                            if (function_exists($name)) {
                                return "{$name}({$arguments})";
                            }
                        }
                    });

                    return $volt;
                },
            ]);

            return $view;
        });
    }
}
