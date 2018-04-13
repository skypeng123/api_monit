<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 18:10
 */

namespace App\Components;


class XhprofClient
{

    public function push($uri,$method,$http_code,$client_ip)
    {

        $xhprof_data = xhprof_disable();

        $run_data = [
            'cmd' => 'PhpStatistics',
            'data' =>[
                'project'=>'doordu_service',
                'uri'=> $uri,
                'method'=>$method,
                'http_code'=>$http_code,
                'req_at'=>time(),
                'client_ip'=>$client_ip,
                'xhprof_id'=>md5(uniqid()),
                'xhprof_data'=>$xhprof_data
            ]
        ];
        $message = json_encode($run_data) . "\r\n\r\n";

        $client = new \swoole_client(SWOOLE_SOCK_UDP);
        $client->connect('127.0.0.1', 9505, 1);
        $client->send($message);
    }
}