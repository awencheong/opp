<?php
/* author: awen, awen_eagle@sina.com,  2015-01-21
 *
 */

if (!defined("APP_LOADED")) {
	define("APP_TIME_ZONE", "Asia/Hong_Kong");
	date_default_timezone_set(APP_TIME_ZONE);
	define("APP_LOADED", true);
	define("APP_ROOT", dirname(realpath(__FILE__)));

	spl_autoload_register(function($class_name) {
		$file_path = APP_ROOT . "/".str_replace("\\", "/", $class_name).".php";
		if (file_exists($file_path)) {
			require($file_path);
		}
	});


	/* App类，作为一个框架支撑所有的基础设施 */

	class App {
		private static $curr_app = null;
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

		public static function curr_app() {
			if (!self::$curr_app) {
				self::$curr_app = new self;
			}
			return self::$curr_app;
		}
	}

	function App() {
		return App::curr_app();
	}


	/* IRouter 接口，封装一个WEB应用所需要的参数 */
	interface IRouter {
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

	/* Router 类， 提供WEB请求的所有参数 */
	class WebRouter implements IRouter {
		private $params = array();
		private $path = false;
		private $format = false;

		public function __construct() {
			if (isset($_SERVER['REQUEST_URI'])) {
				$uri = $_SERVER['REQUEST_URI'];
				if (preg_match('/^(.*)\.(\w+)(\?.*)?$/', $uri, $match)) {
					$this->path = $match[1];
					$this->format = $match[2];

					$params = array_filter(explode("&", trim($match[3], "?")));
					foreach ($params as $p) {
						if (count($p = explode("=", $p)) == 2) {
							list($key, $val) = $p;
							$this->params[$key] = $val;
						}
					}

					foreach ($_POST as $key => $val) {
						$this->params[$key] = $val;
					}

				}
			}
		}

		public function get_format() {
			return $this->format;
		}

		public function get_host() {
			return $_SERVER['HTTP_HOST'];
		}

		public function get_header($header_name = NULL) {
			return "mobile";
		}

		public function get_path() {
			return $this->path;
		}	

		public function get_params() {
			return $this->params;
		}

		public function get_cookie($cookie_name=null) {
			if (!$cookie_name) {
				return $_COOKIE;
			} else if (isset($_COOKIE[$cookie_name])) {
				return $_COOKIE[$cookie_name];
			} else {
				return false;
			}
		}
	}


	/* ICaller ，调用业务模块的接口 */
	interface ICaller {
		public function call($path, $params=array());
		public function set_path($path);
		public function get_result($params = array());
	}

	class ModuleCaller implements ICaller {
		private $path;
		private $root;
		private $dir_root;
		private $loaded = array();

		public function __construct($root, $dir_root) {
			$this->root = $root;
		}

		public function call($path, $params=array()) {
			$this->set_path($path);
			return $this->get_result($params);
		}

		public function set_path($path) {
			$this->path = $path;
		}

		public function get_result($params = array()) {
			$class = str_replace("/", "\\", $this->root . $this->path);
			$file = trim($this->root . "/" . $this->path, "/");
			if (!class_exists($class)) {
				if (isset($this->loaded[$file])) {
					return false;	//已经加载过，没有该类
				}
				$this->loaded[$file] = true;
				if (!class_exists($class)) {
					return false;	//尝试加载，仍然没有该类
				}
			}
			$obj = new $class;
			if (!method_exists($obj, "run")) {
				return false; //该类不存在 run 方法
			}
			return $obj->run($params);
		}

	}

	class PluginCaller implements ICaller {
		public function call($path, $params = array()) {
		}

		public function set_path($path) {
		}

		public function get_result($params = array()) {
		}
	}


	/* IRender ，渲染结果的接口 */
	interface IRender {
		public function render($path, $params=array());
		public function set_path($path);
		public function get_result($params = array());
	}
		

	class HtmlRender implements IRender {
		private $path = false;
		private $smarty = null;
		public function __construct($smarty, $path=null) {
			$this->smarty = $smarty; 
			if ($path) {
				$this->path = $path;
			}
		}

		public function render($path, $params=array()) {
			$this->set_path($path);
			return $this->get_result($params);
		}

		public function set_path($path) {
			$this->path = $path;
		}

		public function get_result($params = array()) {
			foreach ($params as $key => $val) {
				$this->smarty->assign($key, $val);
			}
			return $this->smarty->fetch($this->path, $params);
		}
	}

	class JsonRender implements IRender {
		public function render($path, $params = array()) {
			return $this->get_result($params);
		}

		public function set_path($path) {
		}

		public function get_result($params = array()) {
			return json_encode($params);
		}
	}


	class MysqlPdo {

		private $conn = null;
		private $in_transaction = false;

		public function __construct($host, $port, $user, $passwd, $dbname, $pconn=false){
			$this->conn = new \PDO("mysql:dbname=$dbname;host=$host;port=".intval($port).";", $user, $passwd);
		}

		/*	db query , optional params
		 *	
		 *	@param	sql, string; use "?" to identify params
		 *	@param	params,  array
		 *
		 *	@return array 
		 */
		public function query($sql, array $params=array()){
			if (!$st = $this->conn->prepare($sql)) {
				$this->exception("mysql_pdo::query($sql,".json_encode($params)."),errmsg=".implode("|",$this->conn->errorInfo()).",errno=".$this->conn->errorCode());
			}

			if (!$st->execute($params)) {
				$this->exception("mysql_pdo::query($sql,".json_encode($params)."),errmsg=".implode("|", $st->errorInfo()).",errno=".$st->errorCode());
			}

			if ( $rows = $st->fetchAll(\PDO::FETCH_ASSOC)) {
				/* SELECT some rows */
				return $rows;

			}

			/* SELECT,UPDATE,DELETE,INSERT error */
			if ($rows === false) {
				$this->exception("mysql_pdo::query($sql,".json_encode($params)."),errmsg=".implode("|", $st->errInfo()).",errno=".$st->errorCode());
			}

			/* DELETE, INSERT, UPDATE some rows */
			if ($rows_count = $st->rowCount()) {

				/* rowCount by INSERT, UPDATE, DELETE */
				if ($rows_count == 1 &&
					($insertId = $this->conn->lastInsertId())
				) {
					/* INSERT one row, return id */
					$rows_count = $insertId;

				}
				return array(array($rows_count));

			} else {

				/* SELECT empty row */
				/* DELETE, INSERT, UPDATE no row */
				return array();
			}
		}

		private function exception($errmsg){
			if ($this->in_transaction) {
				$this->conn->rollBack();
				$this->in_transaction = false;
			}
			throw new \Exception($errmsg);
		}


		/*  db query,  optional params
		 *
		 *  @param	sql, string; use "?" to identify params
		 *  @param	params, array
		 *
		 *  @return string
		 */
		public function get_value($sql, array $params=array()){
			if ($result = $this->get_row($sql,$params)) {
				return array_shift($result);
			} else {

				return false;
			}
		}


		/*  db query,  optional params
		 *
		 *  @param	sql, string; use "?" to identify params
		 *  @param	params, array
		 *
		 *  @return array
		 */
		public function get_row($sql, array $params=array()){
			if ($result = $this->query($sql,$params)) {
				return $result[0];
			} else {
				return false;
			}
		}


		/* start transaction
		 *
		 * @return always true
		 */
		public function start_transaction(){
			$this->in_transaction = true;
			if (!$this->conn->beginTransaction()) {
				throw new \Exception("mysql_pdo::start_transaction(".implode("|", $this->conn->errorInfo()).")");
			}
			return true;
		}


		/* commit transaction
		 *
		 * @return  always true
		 */
		public function commit(){
			if (!$this->conn->commit()) {
				throw new \Exception("mysql_pdo::commit(".implode("|", $this->conn->errorInfo()).")");
			}
			$this->in_transaction = false;
			return true;
		}


		/* rollback transaction
		 *
		 * @return	always true
		 */
		public function rollback(){
			if (!$this->conn->rollBack()) {
				throw new \Exception("mysql_pdo::commit(".implode("|", $this->conn->errorInfo()).")");
			}
			$this->in_transaction = false;
			return true;
		}
	}
}


