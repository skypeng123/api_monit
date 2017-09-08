<?php
/**
 * CURL请求类
 *
 * @author jip
 */
namespace App\Components;

use Phalcon\Config\Adapter\Ini as LoadIni;

class Curl
{
    public static $timeout = 5;

    /**
     * 发送GET请求
     * @param $url
     * @return mixed|string
     * @throws Exception
     */
    public static function get($url, $headers = array(), $cookies = '', $userAgent = '', $dataType = 'json')
    {
        return self::curl($url, 'GET', NULL, $headers, $cookies, $userAgent, $dataType);
    }

    /**
     * 发送POST请求
     * @param $url
     * @param $data
     * @return mixed|string
     * @throws Exception
     */
    public static function post($url, $data, $headers = array(), $cookies = '', $userAgent = '', $dataType = 'json')
    {
        return self::curl($url, 'POST', $data, $headers, $cookies, $userAgent, $dataType);
    }

    /**
     * 发送DELETE请求
     * @param $url
     * @param $data
     * @return mixed|string
     * @throws Exception
     */
    public static function delete($url, $data, $headers = array(), $cookies = '', $userAgent = '', $dataType = 'json')
    {
        return self::curl($url, 'DELETE', $data, $headers, $cookies, $userAgent, $dataType);
    }

    /**
     * curl发起HTTP请求
     * @param $url
     * @param string $method
     * @param array $data
     * @return mixed|string
     * @throws Exception
     */
    public static function curl($url, $method = 'GET', $data = '', $headers = array(), $cookies = '', $userAgent = '', $dataType = 'json')
    {
        $start_time = microtime(TRUE);
        if (!empty($data) && is_array($data))
            $data = http_build_query($data);

        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HEADER, 0);
        if ($headers)
            curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        if ($cookies)
            curl_setopt($process, CURLOPT_COOKIE, $cookies);
        if ($userAgent)
            curl_setopt($process, CURLOPT_USERAGENT, $userAgent);
        if (substr($url, 0, 6) == 'https:') {
            curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        switch (strtoupper($method)) {
            case "GET" :
                curl_setopt($process, CURLOPT_HTTPGET, true);
                break;
            case "POST" :
                curl_setopt($process, CURLOPT_POST, 1);
                curl_setopt($process, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE" :
                curl_setopt($process, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($process, CURLOPT_POSTFIELDS, $data);
                break;
        }
        curl_setopt($process, CURLOPT_TIMEOUT, self::$timeout);
        curl_setopt($process, CURLOPT_CONNECTTIMEOUT, self::$timeout);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        $httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
        $curError = curl_errno($process);
        curl_close($process);
        $end_time = microtime(TRUE);

        if ($dataType == 'json') {
            $return_data = json_decode($return, true);
            $return_data = $return_data ? $return_data : $return;
        } else
            $return_data = $return;

        $time = number_format(($end_time - $start_time), 4);

        $app_log = Func::getConfig('app_log');
        if ($httpCode >= 200 && $httpCode < 300) {
            $app_log == 'on' && Func::log('Curl: ' . $time . ' ' . $url . ' ' . $data . ' ' . $httpCode . ' ' . $return, 'debug');
            return $return_data;
        } else {
            $app_log == 'on' && Func::log('Curl: ' . $time . ' ' . $url . ' ' . $data . ' ' . $httpCode . ' ' . $return, 'error');
            return;
        }
    }
}
