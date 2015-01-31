<?php
/* author: awen, awen_eagle@sina.com,  2015-01-21
 *
 */

if (!defined("OPP_LOADED")) {
	define("OPP_TIME_ZONE", "Asia/Hong_Kong");
	date_default_timezone_set(OPP_TIME_ZONE);
	define("OPP_LOADED", true);
	define("OPP_ROOT", dirname(realpath(__FILE__)));

	spl_autoload_register(function($class_name) {
		$file_path = OPP_ROOT . "/".str_replace("\\", "/", $class_name).".php";
		if (file_exists($file_path)) {
			require($file_path);
		}
	});


	/* Opp类，作为一个框架支撑所有的基础设施 */

	class Opp {
		private static $curr_opp = null;
		private $src = array();

		public function __get($name) {
			if (isset($this->src[$name])) {
				return $this->src[$name];
			}
			return false;
		}

		public function __set($name, $resource) {
			$this->src[$name] = $resource;
			return true;
		}

		public function __isset($name){
			return isset($this->src[$name]);
		}

		public static function curr_opp() {
			if (!self::$curr_opp) {
				self::$curr_opp = new self;
			}
			return self::$curr_opp;
		}
	}

	function opp() {
		return Opp::curr_opp();
	}


	/* IRouter 接口，封装一个WEB应用所需要的参数 */
	interface IRequest {
		public function get_host();
		public function get_path();
		public function get_params();
		public function get_format();

		public function get_cookie($cookie_name=null);
		public function get_header($header_name=null);
		/*
		public function get_session($session_name=null);

		public function set_header($header_name, $header_value);
		public function set_cookie($cookie_name, $cookie_value);
		public function set_session($session_name, $session_value);
		 */
	}

	/* ICaller ，调用业务模块的接口 */
	interface ICaller {
		public function call($path, $params=array());
		public function set_path($path);
		public function get_result($params = array());
	}


	/* IRender ，渲染结果的接口 */
	interface IRender {
		public function render($path, $params=array());
		public function set_path($path);
		public function get_result($params = array());
	}

}


