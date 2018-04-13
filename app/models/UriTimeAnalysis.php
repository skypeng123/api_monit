<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class UriTimeAnalysis extends Model
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
        $sql = "SELECT * FROM \"log\".api_time_analysis $where_str ORDER BY $order";

        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }




    public function remove($lid)
    {
        $where = is_array($lid) ? " lid IN (".join(',',$lid).")" : " lid = $lid";
        $sql = "DELETE FROM \"log\".api_time_analysis WHERE $where";
        return $this->di->get('db')->execute($sql);
    }






}