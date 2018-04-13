<?php

namespace App\Admin\Controllers;

use App\Components\Func;
use App\Models\Project;


class ProjectController extends BaseController
{
    /**
     * 列表页
     */
    public function indexAction()
    {
        $this->view->pick('project/index');
    }

    public function getListsAction()
    {
        $keyword = $this->request->get('keyword','trim');

        $where = ['uid'=>$this->uid];
        !empty($keyword) && $where['pname'] = $keyword;
        $order = 'pid DESC';
        $Project = new Project();
        $datalist = $Project->getLists($where,$order);

        $data['data'] = [];
        if(!empty($datalist)){
            foreach($datalist as $key=>$val){

                $data['data'][] = ['pid'=>$val['pid'],'pname'=>trim($val['pname']),'ptag'=>trim($val['ptag']),'created_at'=>$val['created_at']];

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

        $Project = new Project();
        $project_info = $Project->getInfo($pid);
        //sleep(2);

        if(!$project_info)
            self::output(40001,'项目不存在.',null,400);

        if($project_info['uid'] != $this->uid)
            self::output(40003,'没有相关权限.',null,400);



        return self::output(200, 'successed', $project_info);

    }

    /**
     * 获取项目详情
     */
    public function removeAction()
    {
        $pid = $this->request->get('pid','trim');
        if(!$pid)
            self::output(40001,'ID不能为空.',null,400);

        $Project = new Project();
        $id_arr = explode(',',$pid);
        foreach($id_arr as $key=>$val){
            $project_info = $Project->getInfo($val);
            if(!$project_info || $project_info['uid'] != $this->uid)
                unset($id_arr[$key]);
        }

        if(empty($id_arr))
            self::output(40003,'无操作权限.',null,400);

        $rs = $Project->remove($id_arr);

        self::operationLog('项目删除成功',join(',',$id_arr));

        return self::output(200, 'successed',['pid'=>join(',',$id_arr)]);

    }

    public function saveAction()
    {
        $pname = $this->request->get('pname','trim');
        $ptag = $this->request->get('ptag','trim');
        $pid = $this->request->get('pid','int');

        if(!$pname)
            self::output(40001,'项目名称不能为空.',null,400);


        $Project = new Project();
        if($pid){
            $project_info = $Project->getInfo($pid);
            if(!$project_info)
                self::output(40001,'项目不存在.',null,400);

            if($project_info['uid'] != $this->uid)
                self::output(40003,'没有相关权限.',null,400);
        }

        if(!$pid || $pid && $project_info['ptag'] != $ptag){
            $info = $Project->getInfoByPtag($ptag);
            if($info)
                self::output(40001,'项目标识不允许重复.',null,400);
        }

        $dataset = [
            'uid'=>$this->uid,
            'pname'=>$pname,
            'ptag'=>$ptag
        ];

        if($pid){
            $dataset['updated_at'] = date('Y-m-d H:i:s');
            $rs = $Project->updateRow($dataset,$pid);
            $dataset['pid'] = $pid;
        }else{
            $dataset['pname'] = $pname;
            $dataset['created_at'] = date('Y-m-d H:i:s');
            $dataset['pid'] = $Project->createRow($dataset);
        }

        self::operationLog('项目'.($pid ? '修改': '添加').'成功',$dataset['pid']);

        return self::output(200, 'successed', $dataset);

    }



}
