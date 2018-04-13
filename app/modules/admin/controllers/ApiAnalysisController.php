<?php

namespace App\Admin\Controllers;

use App\Models\ProjectTimeAnalysis;
use App\Models\ApiTimeAnalysis;
use App\Models\Project;
use App\Components\Func;

class ApiAnalysisController extends BaseController
{
    public $pid;
    public $project;
    public $project_list;

    public function initialize()
    {

        $pid = $this->request->get('pid','int');

        $Project = new Project();
        $project_list = $Project->getListsByUid($this->uid);
        $project_arr = Func::arrayGroup($project_list,'pid',true);

        if(!$pid || !isset($project_arr[$pid])){
            $project_info = $project_list[0];
        }elseif($pid && isset($project_arr[$pid])){
            $project_info = $project_arr[$pid];
        }
        $this->pid = $project_info['pid'];
        $this->project = ['pid'=>$project_info['pid'],'pname'=>$project_info['pname']];
        $this->project_list = $project_list;
    }

    /**
     * 列表页
     */
    public function indexAction()
    {
        $dateline = date('Y-m-d',strtotime('-1 day'));
        $dateline = '2017-12-19';
        $ProjectTimeAnalysis = new ProjectTimeAnalysis();
        $project_analysis = $ProjectTimeAnalysis->getInfoByPidAndDateline($this->pid,$dateline);

        $this->view->project_analysis = $project_analysis;
        $this->view->project = $this->project;
        $this->view->project_list = $this->project_list;

        $this->view->controller = 'time_analysis';
        $this->view->mod_tag = 'mod_analysis';
        $this->view->controller_name = $this->project['pname'].' - 性能分析';

        $this->view->pick('api_analysis/index');
    }

    public function getListsAction()
    {

        $dateline = date('Y-m-d',strtotime('-1 day'));
        $dateline = '2017-12-19';

        $where['dateline'] = $dateline;
        $where['pid'] = $this->pid;
        $order = 'lid DESC';
        $ApiTimeAnalysis = new ApiTimeAnalysis();
        $datalist = $ApiTimeAnalysis->getLists($where,$order,1,100);

        $output['data'] = [];
        if($datalist){
            foreach($datalist as $key=>$val){
                $output['data'][] = ['pid'=>$val['pid'],'uri'=>trim($val['uri']),'req_y'=>$val['req_y'],'req_t'=>$val['req_t'],'time_y'=>$val['time_y'],'time_t'=>$val['time_t'],'time_max'=>$val['time_t'],'time_min'=>$val['time_t'],'lid'=>$val['lid'],'dateline'=>$val['dateline']];
            }
        }

        return self::output(200, 'successed', $output);

    }

    public function getLineAction()
    {

        $start_time = date('Y-m-01',strtotime('-1 day'));
        $end_time = date('Y-m-d',strtotime(date('Y-m-01',strtotime('+1 month')))-86400);

        $ProjectTimeAnalysis = new ProjectTimeAnalysis();
        $datalist = $ProjectTimeAnalysis->getLine($this->pid,$start_time,$end_time);

        $output['data'] = [];
        if($datalist){
            foreach($datalist as $key=>$val){
                $output['data'][] = ['req_y'=>$val['req_y'],'req_t'=>$val['req_t'],'time_y'=>$val['time_y'],'time_t'=>$val['time_t'],'time_max'=>$val['time_t'],'time_min'=>$val['time_t'],'lid'=>$val['lid'],'dateline'=>date('m-j',strtotime($val['dateline']))];
            }
        }

        return self::output(200, 'successed', $output);

    }

    public function getTop50Action()
    {

        $dateline = date('Y-m-d',strtotime('-1 day'));
        $dateline = '2017-12-19';

        $where['dateline'] = $dateline;
        $where['pid'] = $this->pid;
        $order = 'time_t DESC';
        $ApiTimeAnalysis = new ApiTimeAnalysis();
        $datalist = $ApiTimeAnalysis->getLists($where,$order,1,50);

        $output['data'] = [];
        if($datalist){
            foreach($datalist as $key=>$val){
                $output['data'][] = ['uri'=>trim($val['uri']),'req_y'=>$val['req_y'],'req_t'=>$val['req_t'],'time_y'=>$val['time_y'],'time_t'=>$val['time_t'],'time_max'=>$val['time_t'],'time_min'=>$val['time_t'],'lid'=>$val['lid'],'dateline'=>$val['dateline']];
            }
        }

        return self::output(200, 'successed', $output);

    }


}
