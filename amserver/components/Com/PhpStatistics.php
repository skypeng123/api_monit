<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 15:06
 */

namespace Amserver\Components\Com;

use App\Models\ApiLog;
use App\Models\FuncLog;


class PhpStatistics extends AbstractCom
{

    /**
     * 注入DI
     * @param $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * 获取DI
     * @param $di
     */
    public function getDi()
    {
        return $this->di;
    }


    /**
     * 处理数据
     * @param $data
     */
    public function handle($data)
    {

        $apiLog = new ApiLog();
        $funcLog = new FuncLog();

        foreach ($data['xhprof_data'] as $func => $val) {
            /*页面运行数据*/
            if ($func == 'main()') {

                $dataset = [
                    'uri' => $data['uri'],
                    'method'=>$data['method'],
                    'http_code'=>$data['http_code'],
                    'ptag' => $data['project'],
                    'xhprof_id' => $data['xhprof_id'],
                    'req_ip' => $data['client_ip'],
                    'req_at' => date('Y-m-d H:i:s', $data['req_at']),
                    'wt' => $val['wt'],
                    'cpu' => $val['cpu'],
                    'mu' => $val['mu'],
                    'pmu' => $val['pmu'],
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $apiLog->create($dataset);
            } /*函数运行数据*/
            else {

                $func_arr = explode('==>', $func);

                $dataset = [
                    'func_name' => @$func_arr[1],
                    'parent_func' => @$func_arr[0],
                    'xhprof_id' => $data['xhprof_id'],
                    'ct' => $val['ct'],
                    'wt' => $val['wt'],
                    'cpu' => $val['cpu'],
                    'mu' => $val['mu'],
                    'pmu' => $val['pmu'],
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $funcLog->create($dataset);
            }
        }
    }


}