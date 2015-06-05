<?php
require dirname(__FILE__) . "/consts.php";
require dirname(__FILE__) . "/module.php";
class	App {

	private static $instance = null;

	const LOG_LEV_ERROR = 1;

	private $io = array();

	private $errmsg = null;

	private $mod_root = null;

	public function __set($name, $value) {
		$this->io[$name] = $value;
	}

	public function __get($name) {
		return $this->io[$name];
	}

	public function __isset($name) {
		return isset($this->io[$name]);
	}

	public function set_mod_root($root) {
		$this->mod_root = $root;
	}

	private function _error($errmsg) {
		$this->errmsg = $errmsg;
		$this->_log(self::LOG_LEV_ERROR, $errmsg);
		return false;
	}

	private function _log($level, $msg) {
		switch ($level) {
			case self::LOG_LEV_ERROR: 
				echo $msg;
				break;
			default :
				/* do nothing */
				break;
		}
	}

	public function call() {
		$args = func_get_args();
		if (!$args) {
			return $this->_error("call() func must have at least 1 param");
		}
		$path = array_shift($args);
		$mod = new Module($this->mod_root . "/" . $path);
		try {
			return call_user_func_array(array($mod, "call"), $args);
		} catch (Exception $e) {
			return $this->_error("call() func failed: err" . $e->getMessage());
		}
	}

	public function instance() {
		if (!self::$instance) {
			self::$instance = new Self;
		}
		return self::$instance;
	}

	public function load(array $io) {
		foreach ($io as $name => $obj) {
			$this->io[$name] = $obj;
		}
	}
}

function app() {
	return App::instance();
}
