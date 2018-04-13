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
use App\Components\Mongodb;
use Phalcon\Crypt;
use App\Plugins\Security as SecurityPlugin;

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
                "App\\Components"      => APP_PATH."/components/",
                "App\\Plugins"      => APP_PATH."/plugins/",
                "App\\Models"      => APP_PATH."/models/",
                "App\\Admin\\Controllers" => APP_PATH."/modules/admin/controllers/",
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


        $conn_master = NULL;
        /**
         * 主库连接
         * 避免每次获取pgsql对象时连接数据库，用引用传值+为空判断
         */
        $di->set('db', function () use ($config,$di,&$conn_master) {

            if(empty($conn_master)){
                $conn_master = new \Phalcon\Db\Adapter\Pdo\Postgresql(
                    [
                        'host' => $config->db->host,
                        'port' => $config->db->port,
                        'username' => $config->db->user,
                        'password' => $config->db->password,
                        'dbname' => $config->db->database,
                    ]
                );
            }
            return $conn_master;
        });

        $di->set('redis', function () use ($config){
            $conf = $config['redis'];
            return new Redis($conf['host'], $conf['port'], $conf['auth'], $conf['type'], $conf['timeout'], $conf['is_pconnect']);
        });

        $di->set('mongo', function () use ($config) {
            //$mongo = new \MongoClient($config['mongodb']['host'], (array)$config['mongodb']['options']);
            $mongo = new Mongodb($config['mongodb']['host']);
            $mongo->selectDb("api_minit");
            return $mongo;
        });

        $di->set('view', function() use ($config, $di){
            $view = new View();

            $view->setViewsDir(APP_PATH.'/modules/admin/views/');

            //注册模板引擎
            $view->registerEngines(array(
                //设置模板后缀名
                '.phtml' => function ($view, $di) use ($config) {
                    $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                    $volt->setOptions(array(
                        //模板是否实时编译
                        'compileAlways' => false,
                        //模板编译目录
                        'compiledPath' => ROOT_PATH . '/app/cache/compiled/admin/'
                    ));
                    return $volt;
                },
            ));

            return $view;
        });

        $di->set(
            "crypt",
            function () {
                $crypt = new Crypt();
                $crypt->setKey('#1dpjp8i$=?.//monit$'); // Use your own key!
                return $crypt;
            }
        );

        // Registering a dispatcher
       $di->set(
            "dispatcher",
            function () {
                $eventsManager = new EventsManager();
                /**/
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

                // 监听分发器中使用安全插件产生的事件
                $eventsManager->attach(
                    "dispatch:beforeExecuteRoute",
                    new SecurityPlugin()
                );


                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace('App\Admin\Controllers\\');
                $dispatcher->setEventsManager($eventsManager);
                return $dispatcher;
            }
        );
    }
}
