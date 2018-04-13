<?php
/**
 * 入口
 */


use Phalcon\Mvc\Router;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;
use Phalcon\Config\Factory;
use App\Admin\Module as AdminModule;
use App\Components\Func;
use App\Components\XhprofClient;


// 加载环境设置
define('PRO_ENV', isset($_SERVER['PRO_ENV']) ? $_SERVER['PRO_ENV'] : 'development');
define('TIMESTAMP', microtime(TRUE));
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', dirname(__FILE__));

// 设置错误报告级别
/*    switch (PRO_ENV)
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
}*/

$options = [
    'filePath' => APP_PATH . '/config/' . PRO_ENV . '/config.php',
    'adapter'  => 'php',
];
$config = Factory::load($options);
function_exists('date_default_timezone_set') && date_default_timezone_set($config['timezone']);

if(!empty($config['xhprof_enable'])) {
    require APP_PATH.'/components/xhprof_lib/utils/xhprof_lib.php';
    require APP_PATH.'/components/xhprof_lib/utils/xhprof_runs.php';

    /**
     * XHPROF_FLAGS_NO_BUILTINS  跳过所有的内置函数
     * XHPROF_FLAGS_CPU  添加对CPU使用的分析
     * XHPROF_FLAGS_MEMORY 添加对内存使用的分析
     */
    //
    xhprof_enable(
        XHPROF_FLAGS_MEMORY|XHPROF_FLAGS_CPU,
        [
            'ignored_functions'    => [
                'call_user_func',
                'call_user_func_array'
            ]
        ]
    );
}

require_once ROOT_PATH . '/vendor/autoload.php';


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

try {
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

    $http_code = 502;

    //记录异常
    Func::logException($e);

    if(!empty($config['xhprof_enable'])) {
        $uri = $application->request->getURI();
        $client_ip = $application->request->getClientAddress();
        $method = @$_SERVER['REQUEST_METHOD'];
        $xhprofClient = new XhprofClient();
        $xhprofClient->push($uri,$method, $http_code, $client_ip);
    }

    //抛出异常
    header('HTTP/1.1 502 Bad Request.', TRUE, $http_code);
    echo 'Bad Request. ';
    exit(1); // EXIT_ERROR

}
