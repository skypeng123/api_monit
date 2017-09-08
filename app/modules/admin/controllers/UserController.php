<?php

namespace App\Admin\Controllers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;

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
        $username = $this->request->getPost('username','trim');
        $password = $this->request->getPost('password','trim');

//        if(!$username)
//            self::output(40010,'用户名不能为空.',null,400);
//
//        if(!$password)
//            self::output(40011,'密码不能为空.',null,400);

        $user_info = [
            'uid'=>1,
            'username'=>'test',
            'role'=>1,
        ];

        $config = $this->di->get('config');
        $sign_key = $config['sign_key'];
        $signer = new Sha256();
        $token = (new Builder())->setIssuer($config['site_url'])
        //->setAudience('')
        ->setId(uniqid(), true)
        ->setIssuedAt(time())
        ->setNotBefore(time())
        ->setExpiration(time() + 5)
        ->set('uid', $user_info['uid'])
        ->set('username', $user_info['username'])
        ->set('role', $user_info['role'])
        ->sign($signer, $sign_key)
        ->getToken();

        var_dump($token);

        $data['username'] = $user_info['username'];
        $data['tk'] = $token->getString();
        return self::output(200,'success',$data);
    }

    public function testAction()
    {

    }


}
