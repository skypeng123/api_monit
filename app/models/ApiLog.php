<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class ApiLog extends Model
{

    public function onConstruct()
    {
        $this->setSchema ('admin');
        $this->setSource("api_log");
    }

    /**
     * 获取列表
     * @param $where
     * @param $order
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getLists($where, $order,$page = NULL,$page_size = NULL)
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


        $sql = "SELECT * FROM \"admin\".api_log $where_str ORDER BY $order";

        if(!is_null($page) && !is_null($page_size)){
            $offset = ($page -1)*$page_size;
            $limit = $page_size;
            $sql .= " limit $limit offset $offset";
        }

        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }



    public function remove($lid)
    {
        $where = is_array($lid) ? " lid IN (".join(',',$lid).")" : " lid = $lid";
        $sql = "DELETE FROM \"admin\".api_log WHERE $where";
        return $this->di->get('db')->execute($sql);
    }






}