<?php

use Phalcon\DI\FactoryDefault;
use Phalcon\Cli\Console as ConsoleApp;

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


// Create a console application
$console = new ConsoleApp();
$console->setDI($di);

/**
 * Process the console arguments
 */
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

// Define global constants for the current task and action
define('CURRENT_TASK',   (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    $task = empty($arguments['task']) ? 'Main' : ucfirst($arguments['task']);
    $task .= 'Task';
    $action = empty($arguments['action']) ? 'main' : $arguments['action'];
    $action .= 'Action';
    $className = '\cli\tasks\\' . str_replace("/", "\\", $task);
    $params = isset($arguments['params']) ? $arguments['params'] : [];

    $controller = new $className();
    if (method_exists($controller, $action)) {
        return call_user_func_array(array($controller, $action), $params);
    }

} catch (\Phalcon\Exception $e) {
    echo $e->getMessage().'('.$e->getCode().')';
    exit(1);
}
