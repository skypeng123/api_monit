<?php
/**
 * Created by PhpStorm.
 * project: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class Project extends Model
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
        $sql = "SELECT * FROM \"admin\".project $where_str ORDER BY $order";
        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }

    /**
     * 根据用户获取列表
     * @param $uid
     * @return mixed
     */
    public function getListsByUid($uid)
    {
        $sql = "SELECT * FROM \"admin\".project WHERE uid=:uid ORDER BY pid desc";
        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, ["uid"=>$uid]);
    }

    /**
     * 根据ID查详情
     * @param $projectname
     * @return array
     */
    public function getInfo($pid)
    {
        $sql = "SELECT * FROM \"admin\".project WHERE pid = :pid";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('pid'));
    }

    /**
     * 根据tag查详情
     * @param $projectname
     * @return array
     */
    public function getInfoByPtag($ptag)
    {
        $sql = "SELECT * FROM \"admin\".project WHERE ptag = :ptag";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('ptag'));
    }



    public function remove($pid)
    {
        $where = is_array($pid) ? " pid IN (".join(',',$pid).")" : " pid = $pid";
        $sql = "DELETE FROM \"admin\".project WHERE $where";
        return $this->di->get('db')->execute($sql);
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
        $sql = "INSERT INTO \"admin\".project ($key_str) VALUES ($val_str) returning pid";

        $return = $this->di->get('db')->fetchOne($sql);
        return $return['pid'];
    }

    public function updateRow($dataset,$pid)
    {
        foreach($dataset as $key=>$val){
            $data_arr[] = " $key = '$val'";
        }
        $val_str = join(" , ",$data_arr);
        $sql = "UPDATE \"admin\".project SET $val_str WHERE pid=$pid";

        $return = $this->di->get('db')->execute($sql);
        return $return ? $pid : false;
    }




}