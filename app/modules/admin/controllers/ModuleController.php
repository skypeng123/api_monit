<?php

namespace App\Admin\Controllers;

use App\Models\Module;

class ModuleController extends BaseController
{

    /**
     * 列表页
     */
    public function indexAction()
    {
        $Module = new Module();
        $this->view->parent_list = $Module->getParents();

        $this->view->pick('module/index');
    }

    public function getListsAction()
    {
        $default_mids = $this->request->get('mids','trim');
        $mid_arr = $default_mids ? explode(',',$default_mids) : [];

        $Module = new Module();
        $datalist = $Module->getTree();

        $data = [];
        if($datalist){
            foreach($datalist as $key=>$val){
                $row  = array(
                    "id" => $val['mid'],
                    "text" => $val['mname'],
                    "icon" => "fa fa-folder icon-lg icon-state-success",
                    'type' => 'root',
                    'children'=>false,
                    'state'=>['opened'=>true]
                );
                if(!empty($val['childrens'])){
                    $row['children'] = [];
                    foreach($val['childrens'] as $child){
                        $icon = $child['status'] == 1 ? 'icon-state-warning' : 'icon-state-default';
                        $row['children'][] = [
                            "id" => $child['mid'],
                            "text" => $child['mname'],
                            "icon" => "fa fa-file fa-large $icon",
                            "state" => ["selected"=>in_array($child['mid'],$mid_arr) ? true : false]
                        ];
                    }
                }
                $data[] = $row;
            }
        }

        return self::output(200, 'successed', $data);

    }

    /**
     * 获取详情
     */
    public function getInfoAction()
    {
        $mid = $this->request->get('mid','int');
        if(!$mid)
            self::output(40010,'ID不能为空.',null,400);

        $Module = new Module();
        $info = $Module->getInfo($mid);
        //sleep(2);

        return self::output(200, 'successed', $info);

    }
    /**
     * 获取详情
     */
    public function removeAction()
    {
        $mid = $this->request->get('mid','trim');
        if(!$mid)
            self::output(40010,'id不能为空.',null,400);

        $Module = new Module();
        $id_arr = explode(',',$mid);
        $rs = $Module->remove($id_arr);

        self::operationLog('模块删除成功',$mid);

        return self::output(200, 'successed',['mid'=>$mid]);

    }

    public function saveAction()
    {
        $parent_id = $this->request->get('parent_id','int',0);
        $mname = $this->request->get('mname','trim');
        $mtag = $this->request->get('mtag','trim');
        $icon = $this->request->get('icon','trim');
        $status = $this->request->get('status','int');
        $order_num = $this->request->get('order_num','int');
        $mid = $this->request->get('mid','int');

        if(!$mid && !$mname)
            self::output(40010,'模块名称不能为空.',null,400);

        if(!$mtag)
            self::output(40010,'模块标识不能为空.',null,400);

        $Module = new Module();
        if($mid){
            $info = $Module->getInfo($mid);
            if(!$info)
                self::output(40017,'模块不存在.',null,400);

            if($info['mtag'] != $mtag){
                //检查mtag是否已存在
                $info = $Module->getInfoByMtag($mtag);
                if($info)
                    self::output(40017,'模块标识已经存在.',null,400);
            }

        }else{
            //检查mtag是否已存在
            $info = $Module->getInfoByMtag($mtag);
            if($info)
                self::output(40017,'模块标识已经存在.',null,400);
        }

        $dataset = [
            'parent_id'=>$parent_id,
            'mname'=>$mname,
            'mtag'=>$mtag,
            'icon'=>$icon,
            'order_num'=>$order_num,
            'status'=>$status
        ];

        if($mid){
            $dataset['updated_at'] = date('Y-m-d H:i:s');
            $rs = $Module->updateRow($dataset,$mid);
            $dataset['mid'] = $mid;
        }else{
            $dataset['created_at'] = date('Y-m-d H:i:s');
            $dataset['mid'] = $Module->createRow($dataset);
        }

        self::operationLog('模块'.($mid ? '修改': '添加').'成功',$dataset['mid']);

        return self::output(200, 'successed', $dataset);

    }

}
