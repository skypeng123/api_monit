<?php
/**
 * 辅助函数
 *
 * @author jip 2016-06-29
 */
namespace App\Components;

use Phalcon\Logger\Adapter\Syslog;
use Phalcon\Config\Adapter\Ini as LoadIni;

class Func
{

    public static function getConfig($name = '', $field = '')
    {
        static $config;
        if(empty($config)){
            $config = new LoadIni(APP_PATH.'configs/'.PRO_ENV.'/config.ini');
            $config = (array)$config;
        }

        if($name && $field)
            return isset($config[$name]) && isset($config[$name][$field]) ? $config[$name][$field] : '';
        elseif($name)
            return isset($config[$name]) ? $config[$name] : '';
        else
            return $config;
    }


    /**
     * 返回JSON数据
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return string
     */
    public static function returnJson($code = 200, $msg = '', $data = array())
    {
        if ($code == 200) {
            $return_data = $data;
        } else {
            $return_data = array("code" => $code, "message" => $msg);
            !empty($data) && $return_data['data'] = $data;
        }
        return json_encode($return_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 记录日志
     * @param $message
     * @param string $level
     */
    public static function log($message,$level = 'debug')
    {
        static $logger;

        $config = new LoadIni('configs/'.PRO_ENV.'/config.ini');
        if($config->app_log != 'on')
            return ;

        if($config->log_level!= 'all' && !in_array($level,explode('|',$config->log_level)))
            return ;

        if(empty($logger)){
            $logger = new Syslog("API_MONIT",
                [
                    'option' => LOG_NDELAY,
                    'facility' => LOG_LOCAL0
                ]
            );
        }
        return $logger->$level('['.$level.'] --> '.$message);
    }

    /**
     * 重新组装二维数组
     * @param array $arr
     * @param string $key
     * @param bool $unique 是否保证唯一key值
     * @return array
     */
    public static function arrayGroup($arr, $keystr , $unique = FALSE)
    {
        if (empty($arr))
            return $arr;

        $_result = array();
        foreach ($arr as $key => $item) {
            if (isset($item[$keystr])) {
                $_result[$item[$keystr]][] = $item;
            } else {
                $_result[count($_result)][] = $item;
            }
        }

        $result = array();
        if ($unique) {
            foreach ($_result as $key => $item) {
                $result[$key] = $item[0];
            }
        } else {
            $result = $_result;
        }
        return $result;
    }



    /**
     * 记录异常
     * @param $e
     * @param string $append
     */
    public static function logException($e,$append = '')
    {
        $message = 'Exception: '.$append.' ' . $e->getCode(). ' ' .$e->getMessage().' '.$e->getFile().' '.$e->getLine();

        return self::log($message,'error');
    }

    /**
     * 二维数组排序
     * @param $arr
     * @param $keys
     * @param string $type
     * @return array|bool
     */
    public static function multiArraySort($arr, $keys, $type = "asc")
    {
        if (!is_array($arr)) {
            return false;
        }
        $keysvalue = array ();
        foreach ($arr as $key => $val) {
            $keysvalue[$key] = $val[$keys];
        }
        if ($type == "asc") {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        $keysort = array();
        foreach ($keysvalue as $key => $vals) {
            $keysort[$key] = $key;
        }
        $new_array = array ();
        foreach ($keysort as $key => $val) {
            $new_array[$key] = $arr[$val];
        }
        return $new_array;
    }

}
