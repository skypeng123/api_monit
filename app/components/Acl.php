<?php
/**
 * ACL权限控制
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 11:35
 */

namespace App\Components;

use Phalcon\Mvc\User\Component;
use Phalcon\Acl\Adapter\Memory as AclMemory;
use Phalcon\Acl\Role as AclRole;
use PHalcon\Acl\Resource as AclResource;
use App\Models\Resource;

class Acl extends Component
{
    private $acl;

    private $resourcefile =  'sources.php';
    private $accessListFile =  'accesslist.php';
    private $accessList = array();
    private $privateResource = array(
        'index' => array()
    );

    /*
    所有用户都能访问的资源
    */
    private $allUserResource = array(
        'login' => array(),
        'lock' => array(),
        'error'=> array('show403','show404','show401','show500')
    );

    public function __construct(){

        $resourcefile = APP_PATH . '/cache/acl/' .$this->resourcefile;
        $accessfile = APP_PATH . '/cache/acl/' . $this->accessListFile;

        //如果不存在$sourcefile, 即Resources列表，则访问数据库中菜单表，sf_url为需要
        //进行访问控制的controller名称,并 并且添加到privateResource资源中
        if (!file_exists($resourcefile)) {
            $sources = Resource::find();

            foreach($sources as $source){
                $this->privateResource[$source->rname] = array();
            }
            FileWriter::writeObject($resourcefile, $this->privateResource, true);
        } else{
            $fileArray = require $resourcefile;
            $this->privateResource = $fileArray;
        }

        /**
        如是果没有访问控制列表文件，则建立
         */
        if (!file_exists($accessfile)) {
            $this->buildAccessList();
        } else {

            $this->accessList = require $accessfile;
        }
    }
}