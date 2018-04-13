<?php
/**
 * 配置文件
 */


return [

    //网站域名
    'site_url' => 'http://10.0.2.152:8082/',

    //静态文件URL
    'statics_url' => 'http://10.0.2.152:8082/public/',

    //时区
    'timezone' => 'Etc/GMT-8',

    //Response Content-type
    'content_type' => 'application/json',

    //网站编码
    'charset' => 'utf-8',

    //日志开关
    'log_enable' => 1,

    //日志记录级别 all:debug|info|notice|warning|error|crit|alert|emerg
    'log_level' => 'debug|info|notice|warning|error|crit|alert|emerg',

    //SQL日志开关
    'sql_log_enable' => 1,

    //CSRF开关
    'csrf_enable' => 1,

    //是否开启请求频率验证
    'req_fre_enable' => 1,

    //访问频率检查时间段（秒）
    'req_fre_interval'=>1,

    //访问频率时间段内限制次数
    'req_fre_count'=>10,

    //性能分析开关
    'xhprof_enable'=> 1,

    //性能分析日志机率 1=100%
    'xhprof_rate' => 1,

    'sign_key' => '1f2i3s59loa4idab42dse',

    //postgresql
    'db' => [
        'host' => '10.0.2.152',
        'port'=>5432,
        'user'=>'postgres',
        'password'=>'postgres',
        'database'=>'api_monit',
    ],

    //Redis信息
    'redis' => [
        'type' => 'tcp',
        'host'=>'10.0.0.71',
        'port'=>6379,
        'auth'=>'Doordu2015!!',
        'is_pconnect'=>0,
        'timeout'=>0
    ],

    //mongodb
    'mongodb' => [
        'host'=>'mongodb://127.0.0.1:27017',
        'options' => [],
        'db' => 'api_monit'
    ],

    //beanstalkd
    'beanstalkd' => [
        'host' => '127.0.0.1',
        'port' => 11300
    ],

    //不需要登录认证的页面
    'without_login_uri'=>[
        '/login',
        '/lock',
        '/login/submit',
        '/lock/submit'
    ]
];