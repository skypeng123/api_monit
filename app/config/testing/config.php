<?php
/**
 * 配置文件
 */


return [

    //网站域名
    'site_url' => 'http://10.0.2.152:8082/',

    //静态文件URL
    'statics_url' => 'http://10.0.2.152:8082/public/',

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
    'req_fre_count'=>5,

    //性能分析开关
    'xhprof_enable'=> 1,

    //xhprof ui
    'xhprof_host'=>'http://10.0.2.152/xhprof/xhprof_html',

    //性能分析日志机率 1=100%
    'xhprof_rate' => 1,

    'sign_key' => '1f2i3s59loa4idab42dse',

    //Redis信息
    'redis' => [
        'type' => 'tcp',
        'host'=>'redis.slb.doordu.com',
        'port'=>6379,
        'auth'=>'Doordu2015!!',
        'is_pconnect'=>0,
        'timeout'=>0
    ],

    //mongodb
    'mongodb' => [
        'host'=>'mongodb://127.0.0.1:27017',
        'db' => 'api_monit'
    ],

    //beanstalkd
    'beanstalkd' => [
        'host' => '127.0.0.1',
        'port' => 11300
    ]
];