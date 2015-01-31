<?php
	namespace \env\tools\caller;

	class module implements \ICaller {
		private $path;
		private $space_root;
		private $dir_root;
		private $loaded = array();

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
			$class = str_replace("/", "\\", $this->space_root . $this->path);
			$file = $this->dir_root . "/" . trim($this->path, "/") . ".php";
			if (!class_exists($class)) {
				if (isset($this->loaded[$file])) {
					return false;	//已经加载过，没有该类
				}
				$this->loaded[$file] = true;
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
