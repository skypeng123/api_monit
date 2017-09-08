<?php
/**
 * Redis操作类
 * 
 * 实例化时不连接Redis服务器，实际操作时才建立连接，避免实例化了对象(在入口统一实例化)但不需操作REDIS而浪费连接数
 *
 * @author jip
 */
namespace App\Components;

class Redis{
	
	//redis连接
	private $_redis;

	//redis配置
	private $_redis_conf = array('host' => '', 'port' => '', 'auth' => '', 'type' => '', 'timeout' => 0, 'is_pconnect' => 0);
	

	public function __construct($host = '', $port = '', $auth = '', $type = '', $timeout = 0, $is_pconnect = 0)
	{
		$this -> _init($host, $port, $auth, $type, $timeout, $is_pconnect);
	}
	/**
	 * 初始化
	 */
	private function _init($host, $port, $auth, $type, $timeout, $is_pconnect)
	{
		if (!$this -> _isSupported())
			return FALSE;

		if ($host) {
			$this -> setConfig($host, $port, $auth, $type, $timeout, $is_pconnect);
		}
	}
	
	/**
	 * 设置redis配置信息
	 *
	 * @param string $host 主机名
	 * @param string $port 端口
	 * @param string $timeout 超时时间
	 * @param string $is_pconnect 是否长连
	 * @param string $auth 认证
	 * @param string $type 连接类型：tcp、socket
	 */
	public function setConfig($host = '', $port = '', $auth = '', $type = '', $timeout = 0, $is_pconnect = 0)
	{
		if ($host) {
			$this -> _redis_conf = array('host' => $host, 'port' => $port, 'auth' => $auth, 'type' => $type, 'timeout' => $timeout, 'is_pconnect' => $is_pconnect);
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * 重写方法调用，在方法调用时打开redis连接
	 *
	 * @param string $cmd  调用方法
	 * @param array  $args 调用传参
	 * @return boolean/string/array
	 */
	public function __call($cmd, $args = array())
	{
		try{
			//操作时才连接服务器
			$this -> _open();
			return $this -> _redis ? call_user_func_array(array($this -> _redis, $cmd), $args) : FALSE;
		}catch (\RedisException $e) {
			\dds\libraries\Dfunc::logException($e);
		}
	}
	
	//边接服务器
	private function _open()
	{
		if($this -> _redis)
			return $this -> _redis;


		$this -> _redis = new \Redis();
		$openFunc = !empty($this -> _redis_conf['is_pconnect'])	? 'pconnect' : 'connect';
		if($this -> _redis_conf['type'] === 'socket'){
			//unix domain socket
			$this -> _redis -> $openFunc($this -> _redis_conf['host']);
		}else {
			//tcp connect
			$this -> _redis -> $openFunc($this -> _redis_conf['host'], $this -> _redis_conf['port'], $this -> _redis_conf['timeout']);
		}

		if(!empty($this -> _redis_conf['auth'])){
			$this -> _redis -> auth($this -> _redis_conf['auth']);
		}
		return $this -> _redis;
	}

	private function _isSupported() {
		if (!extension_loaded('redis')) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * 析构函数  自动关闭连接
	 */
	public function __destruct()
	{
		if(!empty($this -> _redis) && empty($this -> _redis_conf['is_pconnect'])){
			$this -> _redis -> close();
		}
	}
	
	
}
