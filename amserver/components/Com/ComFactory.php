<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/27
 * Time: 10:31
 */

namespace Amserver\Components\Com;


class ComFactory
{
    public static function createCom($com){


        $class_name = "Amserver\Components\Com\\$com";

        if(!class_exists($class_name))
            throw new \Exception('Unable to load the com class: '.$class_name);

        return new $class_name();
    }
}