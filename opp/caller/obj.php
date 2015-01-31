<?php
	namespace opp\caller;

/* 加载并执行模块, 需要  
 * 1. 指定 命名空间 根命名;
 * 2. 指定 命名空间 根目录
 * 3. 实现 run( params ) 函数
 *
 * 
 * example :
 *
 * 被调用的模块:
 * /path/to/example_root/do/sth.php 
 *
 * <?php
 *	namespace example\do;
 *
 *  class sth {
 *  	public function run() { echo "hello"; }
 *  }
 *?>
 *
 * 调用：
 * $c = new \opp\caller\obj("example", "/path/to/example_root/");
 * $c->call("do/sth", array("some params"));
 *
 *
 */

	class obj implements \ICaller {
		private $path;
		private $space_root;
		private $dir_root;
		private static $loaded = array();

		public function __construct($space_root, $dir_root) {
			$this->space_root = $space_root;
			$this->dir_root = $dir_root;
		}

		public function call($path, $params=array()) {
			$this->set_path($path);
			return $this->get_result($params);
		}

		public function set_path($path) {
			$this->path = $path;
		}

		public function get_result($params = array()) {
			$class = str_replace("/", "\\", trim($this->space_root,"\\") ."\\". trim($this->path, "\\"));
			$file = $this->dir_root . "/" . trim($this->path, "/") . ".php";
			if (!class_exists($class)) {
				if (isset(self::$loaded[$file])) {
					return false;	//已经加载过，没有该类
				}
				self::$loaded[$file] = true;
				if (!file_exists($file)) {
					return false;
				}
				include $file;
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
