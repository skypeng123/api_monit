<?php

namespace App\Admin;

use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Url;
use Phalcon\Db\Adapter\Pdo\Postgresql;
use App\Components\Redis;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers the module auto-loader
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces(
            [
                "App\\Components"      => APP_PATH."components/",
                "App\\Admin\\Controllers" => APP_PATH."modules/admin/controllers/",
                "App\\Admin\\Models"      => APP_PATH."modules/admin/models/",
            ]
        );

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        $config = $di->get('config');

        $di->set(
            'url',
            function () {
                $url = new Url();
                $url->setBaseUri('/');

                return $url;
            }
        );

        $di->set('redis', function () use ($config){
            $conf = $config['redis'];
            return new Redis($conf['host'], $conf['port'], $conf['auth'], $conf['type'], $conf['timeout'], $conf['is_pconnect']);
        });

        $di->set('mongo', function () use ($config) {
            $mongo = new \MongoClient($config['mongodb']['host'], $config['mongodb']['options']);
            return $mongo;
        });

        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(APP_PATH.'modules/admin/views/');
            return $view;
        });

        // Registering a dispatcher
        $di->set(
            "dispatcher",
            function () {
                $eventsManager = new EventsManager();
                $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
                    switch ($exception->getCode()) {
                        case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                        case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:

                            $dispatcher->forward([
                                'controller' => 'errors',
                                'action'     => 'show404',
                                'params'     => array('message' => $exception->getMessage())
                            ]);
                            return false;
                    }
                });

                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace('App\Admin\Controllers\\');
                $dispatcher->setEventsManager($eventsManager);

                return $dispatcher;
            }
        );
    }
}
