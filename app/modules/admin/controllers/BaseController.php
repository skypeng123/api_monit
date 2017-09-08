<?php

namespace App\Admin\Controllers;

use Phalcon\Mvc\Controller;
use App\Components\Func;

class BaseController extends Controller
{

    public $uid = 0;
    public $username = null;
    public $role = 0;

    public function onConstruct()
    {
        //频率验证
        if ($this->di->get('config')->req_fre_enable === 'on') {
            $redis = $this->di->get('redis');
            //频率限制
            $req_count = $redis->get('api_monit:req_count_'.$this->uid);

            $req_allow_count = $this->di->get('config')->req_fre_count;
            if($req_count >= $req_allow_count){
                return self::output(10506, '访问频率过高', [], 400);
            }else{
                $pipe = $redis->multi();
                $pipe->incr('api_monit:req_count_'.$this->uid);
                $pipe->expire('api_monit:req_count_'.$this->uid,1);
                $pipe->exec();
            }
        }

        $this->view->site_url = $this->di->get('config')->site_url;
        $this->view->statics_url = $this->di->get('config')->statics_url;
    }


    /**
     * 输出JSON
     * @param int $httpStatusCode
     * @param int $errorCode
     * @param string $message
     * @param array $data
     */
    protected function output($errorCode = 200, $message = '', $data = [], $httpStatusCode = 200)
    {
        $return_data = Func::returnJson($errorCode, $message, $data);
        if ($this->di->get('config')->app_log == 'on') {
            self::accessLog($httpStatusCode, $return_data);
        }
        return self::response($httpStatusCode, $return_data);
    }

    /**
     * 记录访问日志
     * @param $httpStatusCode
     * @param $content
     */
    private function accessLog($httpStatusCode, $content)
    {
        $client_data = $this->request->get();
        $log_msg = 'Request: ' . number_format(microtime(TRUE) - TIMESTAMP, 4);
        $log_msg .= ' ' . $this->request->getClientAddress();
        $log_msg .= ' ' . @$client_data['_url'];
        unset($client_data['_url']);
        $log_msg .= ' ' . json_encode($client_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $log_msg .= ' ' . $content;

        if ($httpStatusCode == 200) {
            Func::log($log_msg, 'debug');
        } elseif ($httpStatusCode == 400) {
            Func::log($log_msg, 'warning');
        } else {
            Func::log($log_msg, 'error');
        }
    }

    /**
     * 输出
     * @param $httpStatusCode
     * @param $content
     */
    private function response($httpStatusCode, $content)
    {
        if (PRO_ENV !== 'production') {
            $this->response->setStatusCode($httpStatusCode)
                ->setHeader("Content-Type", "application/json;charset=UTF-8")
                ->setContent($content)
                ->send();
        } else {
            $this->response->setStatusCode($httpStatusCode)
                ->setHeader("Content-Type", "application/json;charset=UTF-8")
                ->setContent($content)
                ->send();
        }
        exit(1);

    }
}
