<?php

namespace App\Admin\Controllers;

use App\Components\Func;
use App\Models\Project;
use App\Models\Monit;

class MonitController extends BaseController
{
    /**
     * 列表页
     */
    public function indexAction()
    {
        $this->view->pick('monit/index');
    }

    public function getListsAction()
    {

        $where = ['uid'=>$this->uid];
        $order = 'pid DESC';
        $Monit = new Monit();
        $datalist = $Monit->getLists($where,$order);

        $monit_type_arr = [1=>'邮件', 2=>'短信', 3=>'电话'];

        $data['data'] = [];
        if(!empty($datalist)){
            foreach($datalist as $key=>$val){
                if($val['alarm_mode']){
                    $type_name_arr = [];
                    $type_arr = explode(',',$val['alarm_mode']);
                    foreach($type_arr as $type){
                        if(isset($monit_type_arr[$type])){
                            $type_name_arr[] = $monit_type_arr[$type];
                        }
                    }
                    $val['alarm_mode'] = join(',',$type_name_arr);
                }else{
                    $val['alarm_mode'] = '';
                }


                $data['data'][] = $val;

            }
        }

        return self::output(200, 'successed', $data);

    }


    /**
     * 获取项目详情
     */
    public function getInfoAction()
    {
        $pid = $this->request->get('pid','int');
        if(!$pid)
            self::output(40001,'pid不能为空.',null,400);


        $Monit = new Monit();
        $monit_info = $Monit->getInfo($pid);

        if(!$monit_info)
            self::output(40001,'项目不存在.',null,400);

        if($monit_info['uid'] != $this->uid)
            self::output(40003,'没有相关权限.',null,400);



        return self::output(200, 'successed', $monit_info);

    }

    /**
     * 获取项目详情
     */
    public function removeAction()
    {
        $pid = $this->request->get('pid','trim');
        if(!$pid)
            self::output(40001,'ID不能为空.',null,400);

        $Monit = new Monit();
        $id_arr = explode(',',$pid);
        foreach($id_arr as $key=>$val){
            $Monit_info = $Monit->getInfo($val);
            if(!$Monit_info || $Monit_info['uid'] != $this->uid)
                unset($id_arr[$key]);
        }

        if(empty($id_arr))
            self::output(40003,'无操作权限.',null,400);

        $rs = $Monit->remove($id_arr);

        self::operationLog('项目删除成功',join(',',$id_arr));

        return self::output(200, 'successed',['pid'=>join(',',$id_arr)]);

    }

    public function saveAction()
    {
        $alarm_email = $this->request->get('alarm_email','trim');
        $alarm_mobile = $this->request->get('alarm_mobile','trim');
        $alarm_mode = $this->request->get('alarm_mode','trim');
        $slow_alarm = $this->request->get('slow_alarm','int',1);
        $error_alarm = $this->request->get('error_alarm','int',1);
        $slow_time = $this->request->get('slow_time','trim',1000);
        $error_status = $this->request->get('error_status','trim',500);
        $pid = $this->request->get('pid','int');

        if(!$pid)
            self::output(40001,'PID不能为空.',null,400);

        $Monit = new Monit();
        $Project = new Project();
        if($pid){
            $pro_info = $Project->getInfo($pid);
            if(!$pro_info)
                self::output(40001,'项目不存在.',null,400);

            if($pro_info['uid'] != $this->uid)
                self::output(40003,'没有相关权限.',null,400);

            $monit_info = $Monit->getRealInfo($pid);
        }

        $dataset = [
            'pid'=>$pid,
            'alarm_mode'=>$alarm_mode,
            'alarm_email'=>$alarm_email,
            'alarm_mobile'=>$alarm_mobile,
            'slow_alarm'=>$slow_alarm,
            'error_alarm'=>$error_alarm,
            'slow_time'=>$slow_time,
            'error_status'=>$error_status,
        ];

        if($pid && !empty($monit_info)){
            $rs = $Monit->updateRow($dataset,$pid);
            $dataset['pid'] = $pid;
        }else{
            $dataset['pid'] = $Monit->createRow($dataset);
        }

        self::operationLog('监控设置'.($pid ? '修改': '添加').'成功',$dataset['pid']);

        return self::output(200, 'successed', $dataset);

    }



}
