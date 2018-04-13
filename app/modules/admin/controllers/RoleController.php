<?php

namespace App\Admin\Controllers;

use App\Models\Role;

class RoleController extends BaseController
{
    /**
     * 列表页
     */
    public function indexAction()
    {
        $this->view->pick('role/index');
    }

    public function getListsAction()
    {
        $keyword = $this->request->get('keyword','trim');

        $where = [];
        !empty($keyword) && $where['rname'] = $keyword;
        $order = 'rid DESC';
        $Role = new Role();
        $datalist = $Role->getLists($where,$order);

        $output['data'] = [];
        if($datalist){
            foreach($datalist as $key=>$val){
                $output['data'][] = ['rid'=>$val['rid'],'rname'=>trim($val['rname']),'created_at'=>$val['created_at']];
            }
        }

        return self::output(200, 'successed', $output);

    }


    /**
     * 获取详情
     */
    public function getInfoAction()
    {
        $rid = $this->request->get('rid','int');
        if(!$rid)
            self::output(40010,'ID不能为空.',null,400);

        $Role = new Role();
        $info = $Role->getInfo($rid);
        //sleep(2);

        return self::output(200, 'successed', $info);

    }

    /**
     * 获取用户详情
     */
    public function removeAction()
    {
        $rid = $this->request->get('rid','trim');
        if(!$rid)
            self::output(40010,'ID不能为空.',null,400);

        $Role = new Role();
        $id_arr = explode(',',$rid);
        foreach($id_arr as $id){
            $rs = $Role->remove($id);
        }

        self::operationLog('用户删除成功',$rid);

        return self::output(200, 'successed',['rid'=>$rid]);

    }

    public function saveAction()
    {
        $rname = $this->request->get('rname','trim');
        $mids = $this->request->get('mids','trim');
        $rid = $this->request->get('rid','int');

        if(!$rid && !$rname)
            self::output(40010,'角色名称不能为空.',null,400);

        $Role = new Role();
        if($rid){
            $info = $Role->getInfo($rid);
            if(!$info)
                self::output(40017,'角色不存在.',null,400);
        }

        $dataset = [
            'rname'=>$rname,
            'mids'=>$mids
        ];

        if($rid){
            $rs = $Role->updateRow($dataset,$rid);
            $dataset['rid'] = $rid;
        }else{
            $dataset['created_at'] = date('Y-m-d H:i:s');
            $dataset['rid'] = $Role->createRow($dataset);
        }

        self::operationLog('角色'.($rid ? '修改': '添加').'成功',$dataset['rid']);

        return self::output(200, 'successed', $dataset);

    }



}
