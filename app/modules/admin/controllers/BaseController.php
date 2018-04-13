<?php

namespace App\Admin\Controllers;

use Phalcon\Mvc\Controller;
use App\Components\Func;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Phalcon\Mvc\Url;
use App\Models\OperationLog;
use App\Models\Module;
use App\Components\XhprofClient;

class BaseController extends Controller
{

    public $uid = 0;
    public $username = null;
    public $role = 0;

    public function onConstruct()
    {
        $config = $this->di->get('config');



        $without_login_uri = (array)$this->di->get('config')->without_login_uri;
        //登录状态验证
        if(!empty($without_login_uri) && !in_array($this->request->getURI(),$without_login_uri)){
            $auth_stat = self::userAuth();
            //$auth_stat = 1;
        }

        //频率验证
        if (!empty($config['req_fre_enable']) && !empty($this->uid)) {
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

        $Module = new Module();
        $module_tree = $Module->getTree();
        $this->view->module_tree = $module_tree;

        $controller = $action = $mod_tag = $controller_name= '';
        if (!empty($_SERVER['REQUEST_URI'])) {
            $uri_parts = explode('?', $_SERVER['REQUEST_URI']);
            $uri = $uri_parts[0];
            $uri_arr = explode('/',$uri);

            $controller = !empty($uri_arr[1]) ? $uri_arr[1] : 'index';
            $action = !empty($uri_arr[2]) ? $uri_arr[2] : 'index';
            $mod_info = $Module->getModuleInfo($controller);

            if($mod_info){
                $mod_tag = $mod_info['mod_tag'];
                $controller_name = $mod_info['controller_name'];
            }
        }

        $this->view->controller = $controller;
        $this->view->action = $action;
        $this->view->mod_tag = $mod_tag;
        $this->view->controller_name = $controller_name;


    }

    /**
     * 用户登录验证
     * @return bool
     */
    protected function userAuth()
    {
        if(!$this->cookies->has('amtk'))
            return -1;

        $tk = $this->cookies->get('amtk')->getValue();

        //token解码
        $token = (new Parser())->parse((string) $tk);
        //签名验证
        $signer = new Sha256();
        if(!$token->verify($signer, $this->di->get('config')->sign_key))
            return -2;

        //nbf 验证
        $not_before = $token->getClaim('nbf');
        if(time() < $not_before)
            return -3;

        //exp TOKEN过期验证
        $expiration = $token->getClaim('exp');
        if(time() > $expiration)
            return -3;

        if(!$token->getClaim('uid') || !$token->getClaim('username') || !$token->getClaim('role'))
            return -4;

        $this->uid = $token->getClaim('uid');
        $this->username = $token->getClaim('username');
        $this->role = $token->getClaim('role');

        return 1;
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

        if(!empty($config['xhprof_enable'])){
            self::pushXhprofStatistics($httpStatusCode);
        }
        return self::response($httpStatusCode, $return_data);
    }

    private function pushXhprofStatistics($httpStatusCode)
    {
        $uri = $this->request->getURI();;
        $client_ip = $this->request->getClientAddress();
        $method = @$_SERVER['REQUEST_METHOD'];

        $xhprofClient = new XhprofClient();
        $xhprofClient->push($uri,$method,$httpStatusCode,$client_ip);

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
            ->setHeader("Access-Control-Allow-Origin", "*")
            ->setContent($content)
            ->send();

        exit(1);

    }

    public function operationLog($message,$ids,$uid = NULL,$username = NULL)
    {
        $OperationLog = new OperationLog();
        $uid = !is_null($uid) ? $uid : $this->uid;
        $username = !is_null($username) ? $username : $this->username;
        return $OperationLog->createLog($uid,$username,$message,$ids);
    }
}
