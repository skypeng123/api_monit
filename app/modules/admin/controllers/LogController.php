<?php

namespace App\Admin\Controllers;

use App\Models\OperationLog;

class LogController extends BaseController
{

    /**
     * 列表页
     */
    public function indexAction()
    {
        $this->view->pick('log/index');
    }

    public function getListsAction()
    {
        $keyword = $this->request->get('keyword','trim');

        $where = [];
        !empty($keyword) && $where['username'] = $keyword;
        $order = 'lid DESC';
        $OperationLog = new OperationLog();
        $datalist = $OperationLog->getLists($where,$order);

        $output['data'] = [];
        if($datalist){
            foreach($datalist as $key=>$val){
                $output['data'][] = ['lid'=>$val['lid'],'username'=>trim($val['username']),'message'=>$val['message'].'['.$val['itemid'].']','created_at'=>$val['created_at']];
            }
        }

        return self::output(200, 'successed', $output);

    }


    /**
     * 获取详情
     */
    public function removeAction()
    {
        $lid = $this->request->get('lid','trim');
        if(!$lid)
            self::output(40010,'ID不能为空.',null,400);

        $OperationLog = new OperationLog();
        $id_arr = explode(',',$lid);
        $rs = $OperationLog->remove($id_arr);

        return self::output(200, 'successed',['lid'=>$lid]);

    }
}
