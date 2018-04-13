<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class OperationLog extends Model
{
    /**
     * 获取列表
     * @param $where
     * @param $order
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getLists($where, $order)
    {
        $where_str = '';
        $where_arr = $params = [];
        if(!empty($where)){
            if(is_array($where)){
                foreach($where as $key=>$val){
                    $where_arr[] = " $key = :$key";
                    $params[$key] = $val;
                }
                $where_str = ' WHERE '.join(' AND ',$where_arr);
            }else{
                $where_str = ' WHERE '.$where;
            }
        }
        $sql = "SELECT * FROM \"log\".operation_log $where_str ORDER BY $order";
        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }




    public function remove($lid)
    {
        $where = is_array($lid) ? " lid IN (".join(',',$lid).")" : " lid = $lid";
        $sql = "DELETE FROM \"log\".operation_log WHERE $where";
        return $this->di->get('db')->execute($sql);
    }


    public function createLog($uid,$username,$message,$ids)
    {

        $itemid = is_array($ids) ? join(',',$ids) : $ids;
        $created_at = date('Y-m-d H:i:s');
        $sql = "INSERT INTO \"log\".operation_log (uid,username,message,itemid,created_at) VALUES ($uid,'$username','$message','$itemid','$created_at') returning lid";

        $return = $this->di->get('db')->fetchOne($sql);
        return $return['lid'];
    }



}