<?php

namespace App\Admin\Controllers;

use Phalcon\Mvc\Controller;
use App\Components\Func;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Phalcon\Mvc\Url;


class BaseController extends Controller
{

    public $uid = 0;
    public $username = null;
    public $role = 0;
    public $without_login_uri = [
        '/user/login',
        '/user/lock',
        '/user/do_login',
        '/user/logout'
    ];

    public function onConstruct()
    {
        //session_start();
        $config = $this->di->get('config');

        //登录状态验证
        if(!in_array($this->request->getURI(),$this->without_login_uri)){
            $auth_stat = self::userAuth();
            var_dump($auth_stat);exit;
            if(!$auth_stat)
                $this->response->redirect('user/login');
        }

        //频率验证
        if (!empty($config['req_fre_enable'])) {
            $redis = $this->di->get('redis');
            //频率限制
            $req_count = $redis->get('api_monit:req_count_'.$this->uid);

            $req_allow_count = $config['req_fre_count'];
            if($req_count >= $req_allow_count){
                return self::output(10506, '访问频率过高', [], 400);
            }else{
                $pipe = $redis->multi();
                $pipe->incr('api_monit:req_count_'.$this->uid);
                $pipe->expire('api_monit:req_count_'.$this->uid,1);
                $pipe->exec();
            }
        }

        $this->view->site_url = $config['site_url'];
        $this->view->statics_url = $config['statics_url'];
    }

    /**
     * 用户登录验证
     * @return bool
     */
    protected function userAuth()
    {

        var_dump($_COOKIE['api_monit_tk']);exit;
        if(empty($_COOKIE['api_monit_tk']))
            return false;

        $tk = $_COOKIE['api_monit_tk'];


        //token解码
        $token = (new Parser())->parse((string) $tk);

        //签名验证
        $signer = new Sha256();
        //var_dump($token->verify($signer, $this->di->get('config')->sign_key));
        if(!$token->verify($signer, $this->di->get('config')->sign_key))
            return false;

        //nbf 验证
        $not_before = $token->getClaim('nbf');
        if(time() < $not_before)
            return false;

        //exp TOKEN过期验证
        $expiration = $token->getClaim('exp');
        //var_dump($expiration);
        if(time() > $expiration)
            return false;

        if(!$token->getClaim('uid'))
            return false;

        $this->uid = $token->getClaim('uid');
        $this->username = $token->getClaim('username');
        $this->role = $token->getClaim('role');
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
        $config = $this->di->get('config');
        if (!empty($config['log_enable'])) {
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
        $log_msg = '[request]';
        $log_msg .= ' ' . $this->request->getClientAddress();
        $log_msg .= ' ' . @$_SERVER['REQUEST_METHOD'];
        $log_msg .= ' ' . $this->request->getURI();
        $log_msg .= ' ' . $httpStatusCode;
        $log_msg .= ' ' . number_format(microtime(TRUE) - TIMESTAMP, 4);
        unset($client_data['_url']);
        $log_msg .= ' ' . http_build_query($client_data);
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
        $config = $this->di->get('config');

        $this->response->setStatusCode($httpStatusCode)
            ->setHeader("Content-Type", $config['content_type'].";charset=".$config['charset'])
            ->setContent($content)
            ->send();

        exit(1);

    }
}
