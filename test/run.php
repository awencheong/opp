<?php
//require "../core/module.php";
require "../core/test.php";
require "../core/consts.php";

class A{};
class B extends A{};
$a = new A;
$b = new B;

app()->db0 = new \opp\mysql();

app()->db0->insert("insert ...");

app()->set_module_root(APP_ROOT . "/modules");
app()->call("ma/run", $a, $b, "here we ");
app()->record("/tmp/ma.run", "ma/run", $a, $b, "here we");
app()->test("/tmp/ma.run", $a, $b, "here we")->should_return(array("awen", "12"));

$r = new __IoRecorder($io, "/tmp/ma.run");
$r->__call($method, $params);

class	__IoRecoder {
	private $io;
	private $data_file;
	private $operation;

	const	READ = 1;
	const	WRITE = 2;
	const	RECORD = 3;

	public function __construct($io, $data_file, $operation) {
		if ($operation != self::READ || $operation != self::WRITE || $operation != self::RECORD) {
			throw new Exception("unknown operation: $operation");
		}
		$this->io = $io;
		$this->data_file = $data_file;
		$this->operation = $operation;
	}
	
	public function __call($name, $params, $operation) {
		if (!$class = get_class($this->io)) {
			throw new Exception("call $name() on a none-object variable");
		}
		if (!method_exists($this->io, $name)) {
			throw new Exception("wrong method {$method}() for class $class");
		}
		return call_user_func_array(array($this->io, $name), $params);
	}
}

class	__HashTable {
	public function __construct($file_path) {
	}
	public function get($key) {
	}
	public function set($key, $val) {
	}
}

class	__BlockStorage {
	public function __construct($contents) {
	}
	private function __read_data($pos, $len) {
	}

	public function allocate($size) {
	}

	public function free($pos) {
	}

	public function write($pos, $str) {
	}

	public function read($pos) {
	}
}


$r = new __TestCaseResult();
$r->should_return($result);
