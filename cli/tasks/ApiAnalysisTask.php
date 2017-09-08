<?php
namespace Cli\Tasks;
/**
 * User: AarioWin10PC
 * Date: 2016/6/28 17:47
 */


class ApiAnalysisTask extends \Phalcon\Cli\Task
{
    protected $mongo;

    public function onConstruct()
    {
        $options = [];
        $this->mongo = new \MongoClient($this->di->get('config')->mongodb->host, $options);

    }


    public function mainAction()
    {
        $config = $this->di->get('config');

        $Queue = new \Phalcon\Queue\Beanstalk([
            'host' => $config->beanstalkd->host,
            'port' => $config->beanstalkd->port,
        ]);

        $Queue->watch('api_analysis_jobs');


        // 循环监听，取 ready 状态的队列
        while (true) {
            if (($Job = $Queue->reserve(30)) !== false) {
                $this->runTask($Job);
            }
        }

        unset($Job);
    }

    /**
     * 处理任务
     * @param \Phalcon\Queue\Beanstalk\Job $Job
     */
    protected function runTask($Job)
    {
        try{
            $job_id = $Job->getId();
            $job_data = $Job->getBody();
            $uri = $job_data['uri'];
            $data = $job_data['data'];
            $dateline = $job_data['dateline'];
            $xhprof_url = $job_data['xhprof_url'];



            if(!$uri || !$data || !$dateline)
                return false;

            $api_data = $data['main()'];
            unset($data['main()']);
            $func_data = array_reverse($data);

            $api_data['pro_id'] = 1;
            $api_data['uri'] = $uri;
            $api_data['xhprof_url'] = $xhprof_url;
            $api_data['dateline'] = date('Y-m-d H:i:s');
            $api_obj = (object)$api_data;
            $collection = $this->mongo->selectCollection($this->di->get('config')->mongodb->db,'api_data');
            $res = $collection->insert($api_obj,['w'=>1]);

            if(!empty($res['ok'])){
                $collection = $this->mongo->selectCollection($this->di->get('config')->mongodb->db,'func_data');
                $batch = new \MongoInsertBatch($collection);
                foreach($func_data as $key=>$val){
                    $val['api_id'] = $api_obj->_id;
                    $val['func_name'] = $key;
                    $val['dateline'] = date('Y-m-d H:i:s');
                    $batch->add($val);
                }
                $ret = $batch->execute(array("w" => 1));
            }
            $Job->delete();

            echo date('Y-m-d H:i:s') . ' Job[' . $job_id . ']:' . $uri . ' 接口性能数据处理成功,任务删除.' . "\n";
            return true;
        }catch (\Exception $e){
            echo date('Y-m-d H:i:s') . ' Job[' . $job_id . ']: ' . $uri . ' 接口性能数据处理失败[' . @$e->getMessage() . '],任务删除.' . "\n";
        }

    }



}