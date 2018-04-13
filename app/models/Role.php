<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class Role extends Model
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
        $sql = "SELECT * FROM \"admin\".role $where_str ORDER BY $order";

        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }


    /**
     * 根据UID查用户信息
     * @param $username
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT * FROM \"admin\".role ORDER BY rid ASC";

        return $this->di->get('db')->fetchAll($sql);
    }

    /**
     * 根据UID查用户信息
     * @param $username
     * @return array
     */
    public function getInfo($rid)
    {
        $sql = "SELECT * FROM \"admin\".role WHERE rid = :rid";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('rid'));
    }


    /**
     * 创建用户
     * @param $user
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
        $sql = "INSERT INTO \"admin\".role ($key_str) VALUES ($val_str) returning rid";

        $return = $this->di->get('db')->fetchOne($sql);
        return $return['rid'];
    }

    public function updateRow($dataset,$rid)
    {
        foreach($dataset as $key=>$val){
            $data_arr[] = " $key = '$val'";
        }
        $val_str = join(" , ",$data_arr);
        $sql = "UPDATE \"admin\".role SET $val_str WHERE rid=$rid";

        $return = $this->di->get('db')->execute($sql);
        return $return ? $rid : false;
    }

    public function remove($rid)
    {
        $sql = "DELETE FROM \"admin\".role WHERE rid = $rid";

        return $this->di->get('db')->execute($sql);
    }

}