<?php

namespace App\Admin\Controllers;

use App\Models\ProjectTimeAnalysis;
use App\Models\ApiTimeAnalysis;
use App\Models\Project;
use App\Components\Func;

class TimeAnalysisController extends BaseController
{

    /**
     * 列表页
     */
    public function indexAction()
    {
        $pid = $this->request->get('pid');

        $Project = new Project();
        $project_list = $Project->getListsByUid($this->uid);
        $project_arr = Func::arrayGroup($project_list,'pid',true);

        if(!$pid || !isset($project_arr[$pid])){
            $project = $project_list[0];
        }elseif($pid && isset($project_arr[$pid])){
            $project = $project_arr[$pid];
        }

        $this->view->pick('time_analysis/index');
    }

    public function getListsAction()
    {

        $Project = new Project();
        $project_list = $Project->getListsByUid($this->uid);
        $project_arr = Func::arrayGroup($project_list,'pid',true);

        $ProjectTimeAnalysis = new ProjectTimeAnalysis();
        $dateline = date('Y-m-d',strtotime('-1 day'));
        $dateline = '2017-12-19';

        $output['data'] = [];
        foreach ($project_list as $key=>$val){
            $info = $ProjectTimeAnalysis->getInfoByPidAndDateline($val['pid'],$dateline);
            $output['data'][] = ['pname'=>trim($val['pname']),'req_y'=>(int)$info['req_y'],'req_t'=>(int)$info['req_t'],'time_y'=>(int)$info['time_y'],'time_t'=>(int)$info['time_t'],'time_max'=>(int)$info['time_t'],'time_min'=>(int)$info['time_t'],'pid'=>$val['pid'],'dateline'=>$dateline];
        }



        return self::output(200, 'successed', $output);

    }


}
