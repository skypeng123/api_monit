<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Admin\Models;

use Phalcon\Mvc\Model;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;

class User extends Model
{

    /**
     * 根据用户名查用户信息
     * @param $username
     * @return array
     */
    public function getInfoByUserName($username)
    {
        $user_info = [
            'uid' => 1,
            'username' => 'test',
            'role' => 1,
            'password'=>md5('123456'.'1234'),
            'salt'=>'1234'
        ];

        return $user_info;
    }


    /**
     * 生成TOKEN
     * @param $user_info
     * @return string|void
     */
    public function generateToken($user_info)
    {
        if (empty($user_info['uid']) || empty($user_info['username']) || empty($user_info['role']))
            return;
        $config = $this->di->get('config');
        $sign_key = $config['sign_key'];
        $signer = new Sha256();
        $token = (new Builder())->setIssuer($config['site_url'])
            //->setAudience('')
            ->setId(uniqid(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + 86400)
            ->set('uid', $user_info['uid'])
            ->set('username', $user_info['username'])
            ->set('role', $user_info['role'])
            ->sign($signer, $sign_key)
            ->getToken();

        return $token->getString();
    }
}