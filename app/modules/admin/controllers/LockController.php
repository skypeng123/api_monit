<?php

namespace App\Admin\Controllers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use App\Models\User;

class LockController extends BaseController
{

    /**
     * 锁定页面
     */
    public function indexAction()
    {
        $this->view->pick('lock/index');
    }

    /**
     * 登录操作
     */
    public function submitAction()
    {
        $username = $this->request->getPost('username', 'trim');
        $password = $this->request->getPost('password', 'trim');

        if(!$username)
            self::output(40010,'用户名不能为空.',null,400);

        if(!$password)
            self::output(40011,'密码不能为空.',null,400);

        $User = new User();

        $user_info = $User->getUserByUserName($username);
        if (empty($user_info))
            self::output(40012, '用户不存在.', null,  400);

        if ($user_info['password'] != md5($password . $user_info['salt']))
            self::output(40013, '帐号密码错误.', null, 400);


        $data['username'] = $user_info['username'];
        $token = $User->generateToken($user_info);

        $this->cookies->set('api_monit_tk',$token,time()+86400);

        self::operationLog('用户登录成功',$user_info['uid']);

        return self::output(200, 'successed', $data);
    }

}
