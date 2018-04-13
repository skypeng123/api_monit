<?php
namespace Amserver\Components\Com;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 15:00
 */
abstract class AbstractCom
{
    /**
     * 注入DI
     * @param $di
     * @return mixed
     */
    abstract public function setDi($di);

    /**
     * 获取DI
     * @param $di
     * @return mixed
     */
    abstract public function getDi();


    /**
     * 处理数据
     * @param $data
     * @return bool
     */
    abstract public function handle($content);
}