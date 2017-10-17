<?php

namespace App\Admin\Controllers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use App\Admin\Models\User;

class UserController extends BaseController
{

    /**
     * 登录页面
     */
    public function loginAction()
    {

    }

    /**
     * 锁定页面
     */
    public function lockAction()
    {

    }

    /**
     * 登录操作
     */
    public function doLoginAction()
    {
        $username = $this->request->getPost('username', 'trim');
        $password = $this->request->getPost('password', 'trim');

        if(!$username)
            self::output(40010,'用户名不能为空.',null,400);

        if(!$password)
            self::output(40011,'密码不能为空.',null,400);

        $User = new User();

        $user_info = $User->getInfoByUserName($username);
        if (empty($user_info))
            self::output(40012, '用户不存在.', null, 400);

        if ($user_info['password'] != md5($password . $user_info['salt']))
            self::output(40013, '帐号密码错误.', null, 400);


        $data['username'] = $user_info['username'];
        $token = $User->generateToken($user_info);

        setcookie('api_monit_tk',$token,time()+86400,'/','.');

        return self::output(200, 'successed', $data);
    }

    public function testAction()
    {

    }


}
