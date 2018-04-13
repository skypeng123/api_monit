<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class Monit extends Model
{
    /**
     * 获取列表
     * @param $where
     * @param $order
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getLists($where, $order, $page = NULL,$page_size = NULL)
    {
        $where_str = '';
        $where_arr = $params = [];
        if(!empty($where)){
            if(is_array($where)){
                foreach($where as $key=>$val){
                    if($key == 'uid'){
                        $where_arr[] = " t1.$key = :$key";
                    }else{
                        $where_arr[] = " t2.$key = :$key";
                    }
                    $params[$key] = $val;
                }
                $where_str = ' WHERE '.join(' AND ',$where_arr);
            }else{
                $where_str = ' WHERE '.$where;
            }
        }


        $sql = "SELECT t1.pid,t1.uid,t1.pname,t2.alarm_mode,t2.slow_alarm,t2.error_alarm FROM \"admin\".project t1 LEFT JOIN  \"admin\".monit t2 ON t1.pid=t2.pid  $where_str ORDER BY t2.$order";

        if(!is_null($page) && !is_null($page_size)){
            $offset = ($page -1)*$page_size;
            $limit = $page_size;
            $sql .= " limit $limit offset $offset";
        }

        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }


    /**
     * 根据ID查详情
     * @param $projectname
     * @return array
     */
    public function getInfo($pid)
    {
        $sql = "SELECT t1.pid,t1.uid,t1.pname,t2.alarm_mode,t2.slow_alarm,t2.error_alarm,t2.alarm_email,t2.alarm_mobile,t2.slow_time,t2.error_status FROM \"admin\".project t1 LEFT JOIN  \"admin\".monit t2 ON t1.pid=t2.pid WHERE t1.pid = :pid";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('pid'));
    }

    public function getRealInfo($pid)
    {
        $sql = "SELECT * FROM \"admin\".monit WHERE pid=:pid";
        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('pid'));
    }



    /**
     * 创建
     * @param $project
     * @return mixed
     */
    public function createRow($dataset)
    {
        foreach($dataset as $key=>$val){
            $keys[] = $key;
            $vals[] = $val;
        }
        $key_str = join(',',$keys);
        $val_str = "'".join("','",$vals)."'";
        $sql = "INSERT INTO \"admin\".monit ($key_str) VALUES ($val_str) returning pid";

        $return = $this->di->get('db')->fetchOne($sql);
        return $return['pid'];
    }

    public function updateRow($dataset,$pid)
    {
        foreach($dataset as $key=>$val){
            $data_arr[] = " $key = '$val'";
        }
        $val_str = join(" , ",$data_arr);
        $sql = "UPDATE \"admin\".monit SET $val_str WHERE pid=$pid";

        $return = $this->di->get('db')->execute($sql);
        return $return ? $pid : false;
    }





}