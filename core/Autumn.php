<?php
/**
* 核心类
* ======
* @author 洪波
* @version 16.02.25
*/
class Autumn
{
	const FRAMEWORK_VERSION = 1.0;
	const DEFAULT_INDEX = 'index.php';
	const DEFAULT_CONTROLLER = 'site';
	const DEFAULT_ACTION = 'index';

	private static $_instance = null;
	private $_config = array();

	private $controller = self::DEFAULT_CONTROLLER;
	private $action = self::DEFAULT_ACTION;
	public $query_params = array();

	private $controller_path = '/app/controllers/';

	public function __construct($config)
	{
		if($config)
		{
			$this->_config = $config;
		}
	}

	public function __get($key)
	{
		return $this->$key;
	}

	public function __set($key, $value)
	{
		$this->$key = $value;
	}

	public static function app($config = array())
	{
		//如果实例不存在，则全新实例化
		if(! (self::$_instance instanceof self))
		{
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}

	public function run()
	{
		$this->parseUrl();
		$this->router();
	}

	/**
	* 注入对象实例
	* ======
	* @param $package 		包名
	* @param $class 		类名
	* @param $class_name 	实例化名称
	* ======
	* @author 洪波
	* @version 16.02.26
	*/
	public function instance($class, $package = 'models',  $class_name = '')
	{
		$file = ROOT . '/app/' . $package . '/' . strtolower($class) . '.php';
		if(file_exists($file))
		{
			require_once($file);
			if($class_name == '')
			{
				$class_name = strtolower($class);
			}
			$class = ucfirst($class);
			$this->$class_name = new $class;
		}
	}

	/**
	* 模块加载器
	* ======
	* @author 洪波
	* @version 15.02.25
	*/
	public function import($package)
	{
		$package = ucfirst($package);
		$paths = array(
			ROOT . '/core/' . $package . '.php',
			ROOT . '/library/' . $package . '.php'
			);
		foreach ($paths as $p)
		{
			if(file_exists($p))
			{
				require_once($p);
			}
		}
	}

	/**
	* 路由器
	* ======
	* @author 洪波
	* @version 15.02.25
	*/
	private function router()
	{
		if($this->controller != '')
		{
			$this->import('Controller');
			$cf = ROOT . $this->controller_path . $this->controller . '.php';
			if(file_exists($cf))
			{
				require_once $cf;
				$class = ucfirst($this->controller);
				$action = 'action' . ucfirst($this->action);
				(new $class)->$action();
			}
		}
	}

	/**
	* url解析解析
	* ======
	* @author 洪波
	* @version 16.02.25
	*/
	private function parseUrl()
	{
		//获取query string
		$url_param = array_filter(explode('/', $_SERVER['PHP_SELF']));
		$parse_count = 2;
		$query = '';
		$index = $this->config('default_index', self::DEFAULT_INDEX);
		foreach ($url_param as $v)
		{
			if($v == $index)
			{
				continue;
			}
			//获取请求参数
			if($parse_count == 0)
			{
				if($query == '')
				{
					$query = $v; 
				}
				else
				{
					$this->query_params[$query] = $v;
					$query = '';
				}
			}
			//获取controller名称
			else if ($parse_count == 2)
			{
				$this->controller = $v;
				-- $parse_count;
			}
			//获取action名称
			else if ($parse_count == 1)
			{
				$this->action = $v;
				-- $parse_count;
			}
		}
	}

	/**
	* 获取系统配置项
	* ======
	* @author 洪波
	* @version 15.02.25
	*/
	public function config($key, $default = '')
	{
		if(isset($this->_config[$key]))
		{
			return $this->_config[$key];
		}
		else
		{
			return $default;
		}
	}
}