<?php
namespace App\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class Security extends Plugin
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {

        $without_login_uri = (array)$this->di->get('config')->without_login_uri;
        //登录状态验证
        if(!empty($without_login_uri) && !in_array($this->request->getURI(),$without_login_uri)){
            $auth_stat = self::userAuth();
            //登录状态过期跳转到锁定页
            if($auth_stat == -3)
                $this->response->redirect('lock');
            elseif($auth_stat != 1)
                $this->response->redirect('login');
        }

        $controllerName = $dispatcher->getControllerName();
        $actionName = $dispatcher->getActionName();


        $acl = new AclList();

        //设置默认访问级别为拒绝
        $acl->setDefaultAction(\Phalcon\Acl::DENY);


        // 创建角色
        $roleSuperAdmin = new \Phalcon\Acl\Role("SuperAdmin", "超级管理员");
        $GeneralAdmin = new \Phalcon\Acl\Role("GeneralAdmin", "普通管理员");

        $acl->addRole($roleSuperAdmin);
        $acl->addRole($GeneralAdmin);

        //定义资源
        $userResource = new \Phalcon\Acl\Resource("User");
        $acl->addResource($userResource, ['index', 'save','remove']);

        $roleResource = new \Phalcon\Acl\Resource("Role");
        $acl->addResource($roleResource, ['index', 'save','remove']);

        $moduleResource = new \Phalcon\Acl\Resource("Module");
        $acl->addResource($moduleResource, ['index', 'save','remove']);

        $logResource = new \Phalcon\Acl\Resource("Log");
        $acl->addResource($logResource, ['index', 'remove']);


        //设置访问权限
        $acl->allow("SuperAdmin", "User", ['index', 'save','remove']);
        $acl->allow("SuperAdmin", "Role", ['index', 'save','remove']);
        $acl->allow("SuperAdmin", "Module", ['index', 'save','remove']);
        $acl->allow("SuperAdmin", "Log", ['index', 'remove']);

        $acl->deny("GeneralAdmin", "User", ['index', 'save','remove']);
        $acl->deny("GeneralAdmin", "Role", ['index', 'save','remove']);
        $acl->deny("GeneralAdmin", "Module", ['index', 'save','remove']);
        $acl->deny("GeneralAdmin", "Log", ['index', 'remove']);




/*        //如果资源是定义在acl的privateResource数组中，则为私有资源，而如果是公开的资源
        //则直接返回true
        if (!$acl->isPrivate($controllerName)) {
            return true;
        }

        //如果用户为管理员，则拥有所有的权限
        //直接返回true
        if ($this->role == 1) {
            return true;
        }

        //如果没有权限，则重定向到403页面
        if(!$acl->isAllowed($this->uid, $controllerName, $actionName)){

            $dispatcher->forward(array(
                'controller' => 'errors',
                'action'     => 'show403'
            ));

            return false;
        }*/
    }

    /**
     * 用户登录验证
     * -1 用户未登录，-2 签名验证失败，-3 有效期验证失败，-4 缺少必要参数
     * @return bool
     */
    public function userAuth()
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
}