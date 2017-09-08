<?php
/**
 * 入口
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use App\Admin\Module as AdminModule;
use Phalcon\Config\Factory;

try {
    // 加载环境设置
    define('PRO_ENV', isset($_SERVER['PRO_ENV']) ? $_SERVER['PRO_ENV'] : 'development');
    define('TIMESTAMP', microtime(TRUE));
    define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
    define('APP_PATH', dirname(__FILE__) . '/');

    // 设置错误报告级别
    switch (PRO_ENV)
    {
        case 'development':
            error_reporting(-1);
            ini_set('display_errors', 1);
            break;
        case 'testing':
        case 'production':
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            break;
        default:
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'The application environment is not set correctly.';
            exit(1); // EXIT_ERROR
    }

    $options = [
        'filePath' => APP_PATH . 'config/' . PRO_ENV . '/config.php',
        'adapter'  => 'php',
    ];
    $config = Factory::load($options);
    function_exists('date_default_timezone_set') && date_default_timezone_set($config['timezone']);

    require_once ROOT_PATH . 'vendor/autoload.php';

    $di = new FactoryDefault();

    $di->set('config',function() use ($config){
        return $config;
    });

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
    header('HTTP/1.1 502 Bad Request.', TRUE, 502);
    echo 'Exception: ', $e->getMessage();
    exit(1); // EXIT_ERROR

}
