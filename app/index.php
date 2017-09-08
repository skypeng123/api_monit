<?php

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use App\Admin\Module as AdminModule;
use Phalcon\Config\Adapter\Ini as LoadIni;

// 加载环境设置
$env_config = new LoadIni('configs/env.ini');
define('PRO_ENV', $env_config->env);
define('TIMESTAMP', microtime(TRUE));
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('APP_PATH', dirname(__FILE__) . '/');

// 设置错误报告级别
if (PRO_ENV == 'production')
    error_reporting(0);
else
    error_reporting(E_ALL);

function_exists('date_default_timezone_set') && date_default_timezone_set($env_config->timezone);

$di = new FactoryDefault();

$di->set('config', function () {
    return new LoadIni('configs/' . PRO_ENV . '/config.ini');
});

// Registering a router
$di->set(
    "router",
    function () {
        $router = new Router();
        $router->setDefaultModule("admin");
        $route = $router->add(
            "/:controller/:action",
            [
                "module" => "admin",
                "controller" => 1,
                "action" => 2,
            ]
        );
        $route->convert(
            "action",
            function ($action) {
                //action大写转下划线
                return preg_replace_callback(
                    '|\_[a-zA-Z]+|',
                    create_function(
                        '$matches',
                        'return ucwords(substr($matches[0],1));'
                    ),
                    $action
                );
            }
        );
        return $router;
    }
);

$application = new Application($di);
try {
    // Register the installed modules
    $application->registerModules(
        [
            "admin" => [
                "className" => AdminModule::class,
                "path" => "./modules/admin/Module.php",
            ],
        ]
    );

    $response = $application->handle();
    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
