<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 17:11
 */

return [
    //时区
    'timezone' => 'Etc/GMT-8',

    //udp server
    'udp_server'=>[
        'host'=>'127.0.0.1',
        'port'=>9505,
        'timeout' => 0.5,
        'daemonize'=>false,
        'worker_num' => 4,
        'task_worker_num'=>2,
        'open_eof_check'=>true,
        'package_eof'=>'\r\n\r\n',
    ],

    //postgresql
    'db' => [
        'host' => '10.0.2.152',
        'port'=>5432,
        'user'=>'postgres',
        'password'=>'postgres',
        'database'=>'api_monit',
    ],
];