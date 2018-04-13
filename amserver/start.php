<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 16:51
 */
use Phalcon\DI\FactoryDefault;
use Phalcon\Config\Factory;
use Amserver\Components\Protocol\JsonProtocol;
use Amserver\Components\Com\ComFactory;

try {

    // 加载环境设置
    define('PRO_ENV', isset($_SERVER['PRO_ENV']) ? $_SERVER['PRO_ENV'] : 'development');
    define('TIMESTAMP', microtime(TRUE));
    define('ROOT_PATH', dirname(dirname(__FILE__)));
    define('APP_PATH', dirname(__FILE__));

    $loader = require_once(ROOT_PATH . '/vendor/autoload.php');

    $namespaces = [
        'Amserver\\Components\\' =>[APP_PATH . '/components'],
        'App\\Models\\' => [ROOT_PATH . '/app/models'],
        'App\\Components\\' => [ROOT_PATH . '/app/components'],
    ];

    foreach ($namespaces as $key => $val) {
        $loader->setPsr4($key, $val);
    }


    //加载配置
    $options = [
        'filePath' => APP_PATH . '/config/' . PRO_ENV . '/config.php',
        'adapter' => 'php',
    ];
    $config = Factory::load($options);

    function_exists('date_default_timezone_set') && date_default_timezone_set($config['timezone']);


    $di = new FactoryDefault();

    $di->set('config',function() use ($config){
        return $config;
    });

    $conn_master = NULL;
    /**
     * 数据库连接
     * 避免每次获取pgsql对象时连接数据库，用引用传值+为空判断
     */
    $di->set('db', function () use ($config, $di, &$conn_master) {

        if (empty($conn_master)) {
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


    //启动服务
    $setting = (array)$config->udp_server;
    $server = new swoole_server($setting['host'], $setting['port'], SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
    $server->set($setting);


    $server->on('Packet', function ($server, $data, $clientInfo) use ($di) {

        //$server->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);

        file_put_contents('/tmp/amserver.log', $data, FILE_APPEND);

        $data = JsonProtocol::decode($data);

        $server->task($data);



    });

    $server->on('Task', function ($server, $task_id, $from_id, $data) use ($di) {

        //实例化指令处理类
        $Com = ComFactory::createCom(ucwords($data['cmd']));

        //注入
        $Com->setDi($di);

        //处理数据
        $rs = $Com->handle($data['data']);

        return 'result.';
    });

    $server->on('Finish', function ($serv, $task_id, $data) {

    });

    $server->start();

} catch (\Exception $e) {

}
