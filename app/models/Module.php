<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;

class Module extends Model
{
    /**
     * 获取列表
     * @param $where
     * @param $order
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getLists($where, $order, $page = 1,$limit = 10)
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
        $offset = (($page < 1 ? 1 : $page) - 1)*$limit;
        $sql = "SELECT * FROM \"admin\".module $where_str ORDER BY $order LIMIT $limit OFFSET $offset";

        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }

    public function getTree()
    {
        $sql = "SELECT mid,parent_id,mname,mtag,order_num,status,icon FROM \"admin\".module WHERE parent_id=0 ORDER BY order_num ASC";
        $parent_list = $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
        if($parent_list){
            foreach($parent_list as $key=>$val){
                $parent_list[$key]['childrens'] = self::getChildrens($val['mid']);
            }
        }
        return $parent_list;
    }

    private function getChildrens($parent_id)
    {
        $sql = "SELECT mid,parent_id,mname,mtag,order_num,status,icon FROM \"admin\".module WHERE parent_id=:parent_id ORDER BY order_num ASC";
        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC,compact('parent_id'));
    }

    public function getParents()
    {
        $sql = "SELECT mid,parent_id,mname,mtag,order_num,status,icon FROM \"admin\".module WHERE parent_id=0 ORDER BY order_num ASC";
        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC);
    }

    public function getModuleInfo($controller)
    {
        $sql = "SELECT mid,parent_id,mtag,mname FROM \"admin\".module WHERE mtag = :controller";
        $info = $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('controller'));
        if(!empty($info['parent_id'])){
            $sql = "SELECT mid,parent_id,mtag,mname FROM \"admin\".module WHERE mid = :mid";
            $data = $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, ['mid'=>$info['parent_id']]);
            return ['mod_tag'=>$data['mtag'],'controller_name'=>$info['mname']];
        }else{
            return ['mod_tag'=>$info['mtag'],'controller_name'=>$info['mname']];
        }
    }
    /**
     * 根据UID查用户信息
     * @param $username
     * @return array
     */
    public function getInfo($mid)
    {
        $sql = "SELECT * FROM \"admin\".module WHERE mid = :mid";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('mid'));
    }

    public function getInfoByMtag($mtag)
    {
        $sql = "SELECT * FROM \"admin\".module WHERE mtag = :mtag";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('mtag'));
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
        $sql = "INSERT INTO \"admin\".module ($key_str) VALUES ($val_str) returning mid";

        $return = $this->di->get('db')->fetchOne($sql);
        return $return['mid'];
    }

    public function updateRow($dataset,$mid)
    {
        foreach($dataset as $key=>$val){
            $data_arr[] = " $key = '$val'";
        }
        $val_str = join(" , ",$data_arr);
        $sql = "UPDATE \"admin\".module SET $val_str WHERE mid=$mid";

        $return = $this->di->get('db')->execute($sql);
        return $return ? $mid : false;
    }

    public function remove($mid)
    {
        $where = is_array($mid) ? " mid IN (".join(',',$mid).")" : " mid = $mid";
        $sql = "DELETE FROM \"admin\".module WHERE $where";
        return $this->di->get('db')->execute($sql);
    }

}