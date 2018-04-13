<?php

namespace App\Admin\Controllers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use App\Models\User;

class LogoutController extends BaseController
{


    /**
     * 登录操作
     */
    public function indexAction()
    {


        $this->cookies->set('api_monit_tk','',time()-86400);

        self::operationLog('用户退出登录成功',$this->username);

        $this->response->redirect('/login');

        //return self::output(200, 'successed');
    }
}
