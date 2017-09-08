<?php
/**
 * 认证授权
 * User: AarioWin10PC
 * Date: 2016/6/22 14:41
 */
namespace App\Admin\Controllers;

use App\Components\Func;

class Oauth2Controller extends BaseController
{
    /**
     * @SWG\Definition(
     *     definition="Oauth2SuccessModel",
     *     type="object",
     *     @SWG\Property(property="access_token", type="string",default="string"),
     *     @SWG\Property(property="expires_in", type="integer",default="86400"),
     *     @SWG\Property(property="refresh_token", type="string",default="string")
     * ),
     * @SWG\Definition(
     *     definition="Oauth2ErrorModel",
     *     required={"code","msg"},
     *     @SWG\Property(
     *         property="code",
     *         type="integer",
     *         format="int32",
     *     ),
     *     @SWG\Property(
     *         property="message",
     *         type="string"
     *     ),
     *
     * ),
     * @SWG\Get(
     *     path="/common/v1/oauth2/access_token",
     *     summary="获取接口访问凭证",
     *     tags={"Oauth2"},
     *     description="所有接口都需要带上此接口获取的access_token进行访问.",
     *     operationId="access_token",
     *     consumes={"text/html"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="appid",
     *         in="query",
     *         description="应用唯一标识",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="secret",
     *         in="query",
     *         description="给应用分配的密钥",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="guid",
     *         in="query",
     *         description="终端设备唯一标识",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="version",
     *         in="query",
     *         description="应用版本",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="请求成功",
     *         @SWG\Schema(ref="#/definitions/Oauth2SuccessModel")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="客户端错误 错误码：10401 无访问权限， 10400 缺少必要参数",
     *         @SWG\Schema(ref="#/definitions/Oauth2ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="服务器端错误",
     *         @SWG\Schema(ref="#/definitions/Oauth2ErrorModel")
     *     )
     * )
     */
    public function accessTokenAction()
    {
        $appid = $this->request->get('appid','trim');
        $secret = $this->request->get('secret','trim');

        //授权校验
        if(!self::_checkAccount($appid,$secret)){
            return self::output2(10401, '无访问权限.', [], 400);
        }
        //参数校验
        if(empty($appid) || empty($secret)){
            return self::output2(10400, '缺少必要参数.', [], 400);
        }

        $access_token_expires_in = Func::getSetting('oauth2','access_token_expires_in');
        $refresh_token_expires_in = Func::getSetting('oauth2','refresh_token_expires_in');
        $refresh_token = md5('rtk'.$appid.uniqid());
        $access_token = md5('atk'.$appid.uniqid());
        $redis = $this->di->get('redis');
        $pipe = $redis->multi();
        $pipe->set('ddservice:refresh_token_'.$refresh_token,$access_token,$refresh_token_expires_in);
        $pipe->set('ddservice:access_token_'.$access_token,$appid,$access_token_expires_in);
        $pipe->exec();

        $data = [
            'access_token'=>$access_token,
            'expires_in'=>$access_token_expires_in,
            'refresh_token'=>$refresh_token
        ];

        return $this->output2(200, '请求成功.', $data, 200);

    }

    /**
     * @SWG\Get(
     *     path="/common/v1/oauth2/refresh_token",
     *     summary="刷新接口访问凭证",
     *     tags={"Oauth2"},
     *     description="刷新接口访问凭证access_token.",
     *     operationId="refresh_token",
     *     consumes={"text/html","application/json"},
     *     produces={"text/html","application/json"},
     *     @SWG\Parameter(
     *         name="appid",
     *         in="query",
     *         description="应用唯一标识",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="guid",
     *         in="query",
     *         description="终端设备唯一标识",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Parameter(
     *         name="refresh_token",
     *         in="query",
     *         description="刷新凭证",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="请求成功",
     *         @SWG\Schema(ref="#/definitions/Oauth2SuccessModel")
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="客户端错误 错误码：10401 无访问权限， 10400 缺少必要参数,10502 refresh_token已过期",
     *         @SWG\Schema(ref="#/definitions/Oauth2ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="500",
     *         description="服务器端错误",
     *         @SWG\Schema(ref="#/definitions/Oauth2ErrorModel")
     *     )
     * )
     */
    public function refreshTokenAction()
    {
        $appid = $this->request->get('appid');
        $refresh_token = $this->request->get('refresh_token');

        //授权校验
        if(!self::_checkAccount($appid)){
            return $this->output2(10401, '无访问权限.', [], 400);
        }
        //参数校验
        if(empty($appid) || empty($refresh_token)){
            return $this->output2(10400, '缺少必要参数.', [], 400);
        }


        $redis = $this->di->get('redis');
        $access_token = $redis->get('ddservice:refresh_token_'.$refresh_token);
        if(!$access_token){
            return $this->output2(10502, 'refresh_token已过期.', [], 400);
        }

        $access_token_expires_in = Func::getSetting('oauth2','access_token_expires_in');
        $refresh_token_expires_in = Func::getSetting('oauth2','refresh_token_expires_in');
        $pipe = $redis->multi();
        $pipe->set('ddservice:refresh_token_'.$refresh_token,$access_token,$refresh_token_expires_in);
        $pipe->set('ddservice:access_token_'.$access_token,$appid,$access_token_expires_in);
        $pipe->exec();

        $data = [
            'access_token'=>$access_token,
            'expires_in'=>$access_token_expires_in,
            'refresh_token'=>$refresh_token
        ];

        return $this->output2(200, '请求成功.', $data, 200);
    }

    /**
     * 校验APPID,SECRET
     * @param $appid
     * @param string $secret
     * @return bool
     */
    private function _checkAccount($appid,$secret = null)
    {
        $accounts = Func::getSetting('oauth2','accounts');
        $account_arr = Func::arrayGroup($accounts,'appid',true);
        if(!is_null($secret)){
            if(isset($account_arr[$appid]) && $account_arr[$appid]['secret'] == $secret){
                return true;
            }
        }else{
            if(isset($account_arr[$appid])){
                return true;
            }
        }
        return false;
    }


}

