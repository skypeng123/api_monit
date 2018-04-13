<?php

namespace App\Admin\Controllers;

use App\Components\Func;
use App\Models\User;
use App\Models\Role;

class UserController extends BaseController
{
    /**
     * 列表页
     */
    public function indexAction()
    {
        $Role = new Role();
        $this->view->role_list = $Role->getAll();

        $this->view->pick('user/index');
    }

    public function getListsAction()
    {
        $keyword = $this->request->get('keyword','trim');

        $where = [];
        !empty($keyword) && $where['username'] = $keyword;
        $order = 'uid DESC';
        $User = new User();
        $datalist = $User->getLists($where,$order);

        $Role = new Role();
        $role_list = $Role->getAll();
        $role_arr = Func::arrayGroup($role_list,'rid',true);

        $status_arr = [1=>'启用',2=>'停用'];

        $data['data'] = [];
        if(!empty($datalist)){
            foreach($datalist as $key=>$val){
                $role_name = $role_arr[$val['role']]['rname'];
                $status_str = $status_arr[$val['status']];
                $data['data'][] = ['uid'=>$val['uid'],'username'=>trim($val['username']),'role'=>$role_name,'mobile'=>$val['mobile'],'status'=>$status_str,'created_at'=>$val['created_at']];

            }
        }

        return self::output(200, 'successed', $data);

    }


    /**
     * 获取用户详情
     */
    public function getInfoAction()
    {
        $uid = $this->request->get('uid','int');
        if(!$uid)
            self::output(40001,'uid不能为空.',null,400);

        $User = new User();
        $userinfo = $User->getInfo($uid);
        //sleep(2);

        return self::output(200, 'successed', $userinfo);

    }

    /**
     * 获取用户详情
     */
    public function removeAction()
    {
        $uid = $this->request->get('uid','trim');
        if(!$uid)
            self::output(40001,'ID不能为空.',null,400);

        $User = new User();
        $id_arr = explode(',',$uid);
        $rs = $User->remove($id_arr);

        self::operationLog('用户删除成功',$uid);

        return self::output(200, 'successed',['uid'=>$uid]);

    }

    public function saveAction()
    {
        $username = $this->request->get('username','trim');
        $password = $this->request->get('password','trim');
        $mobile = $this->request->get('mobile','trim');
        $role = $this->request->get('role','int');
        $status = $this->request->get('status','int');
        $uid = $this->request->get('uid','int');

        if(!$uid && !$username)
            self::output(40001,'用户名不能为空.',null,400);

        if(!$uid && !$password)
            self::output(40001,'密码不能为空.',null,400);

        if(!$mobile)
            self::output(40001,'手机号码不能为空.',null,400);

        if(!$role)
            self::output(40001,'角色不能为空.',null,400);

        $User = new User();
        if(!$uid){
            //添加新用户时检查用户名
            $user_info = $User->getInfoByUserName($username);
            if($user_info)
                self::output(40001,'用户名已经存在.',null,400);
        }else{
            $user_info = $User->getInfo($uid);
            if(!$user_info)
                self::output(40001,'用户不存在.',null,400);
        }

        $dataset = [
            'mobile'=>$mobile,
            'role'=>$role,
            'status'=>$status
        ];

        if($uid){
            //新密码
            if($user_info['password'] != md5($password.$user_info['salt'])){
                $dataset['salt'] = mt_rand(100000,999999);
                $dataset['password'] = md5($password.$dataset['salt']);
            }
            $dataset['updated_at'] = date('Y-m-d H:i:s');
            $rs = $User->updateRow($dataset,$uid);
            $dataset['uid'] = $uid;
        }else{
            $dataset['username'] = $username;
            $salt = mt_rand(100000,999999);
            $dataset['salt'] = $salt;
            $dataset['password'] = md5($password.$salt);
            $dataset['created_at'] = date('Y-m-d H:i:s');
            $dataset['uid'] = $User->createRow($dataset);
        }

        self::operationLog('用户'.($uid ? '修改': '添加').'成功',$dataset['uid']);

        return self::output(200, 'successed', $dataset);

    }



}
