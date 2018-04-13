<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 10:54
 */

namespace App\Models;

use Phalcon\Mvc\Model;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;

class User extends Model
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
        $sql = "SELECT * FROM \"admin\".user $where_str ORDER BY $order";
        return $this->di->get('db')->fetchAll($sql, \Phalcon\Db::FETCH_ASSOC, $params);
    }
    /**
     * 根据UID查用户信息
     * @param $username
     * @return array
     */
    public function getInfo($uid)
    {
        $sql = "SELECT * FROM \"admin\".user WHERE uid = :uid";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('uid'));
    }

    /**
     * 根据用户名查用户信息
     * @param $username
     * @return array
     */
    public function getInfoByUserName($username)
    {
        $sql = "SELECT * FROM \"admin\".user WHERE username = :username";

        return $this->di->get('db')->fetchOne($sql, \Phalcon\Db::FETCH_ASSOC, compact('username'));
    }

    public function remove($uid)
    {
        $where = is_array($uid) ? " uid IN (".join(',',$uid).")" : " uid = $uid";
        $sql = "DELETE FROM \"admin\".user WHERE $where";
        return $this->di->get('db')->execute($sql);
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
        $sql = "INSERT INTO \"admin\".user ($key_str) VALUES ($val_str) returning uid";

        $return = $this->di->get('db')->fetchOne($sql);
        return $return['uid'];
    }

    public function updateRow($dataset,$uid)
    {
        foreach($dataset as $key=>$val){
            $data_arr[] = " $key = '$val'";
        }
        $val_str = join(" , ",$data_arr);
        $sql = "UPDATE \"admin\".user SET $val_str WHERE uid=$uid";

        $return = $this->di->get('db')->execute($sql);
        return $return ? $uid : false;
    }





    /**
     * 生成TOKEN
     * @param $user_info
     * @return string|void
     */
    public function generateToken($user_info)
    {
        if (empty($user_info['uid']) || empty($user_info['username']) || empty($user_info['role']))
            return;
        $config = $this->di->get('config');
        $sign_key = $config['sign_key'];
        $signer = new Sha256();
        $token = (new Builder())->setIssuer($config['site_url'])
            //->setAudience('')
            ->setId(uniqid(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + 86400)
            ->set('uid', $user_info['uid'])
            ->set('username', $user_info['username'])
            ->set('role', $user_info['role'])
            ->sign($signer, $sign_key)
            ->getToken();

        return $token->getString();
    }
}