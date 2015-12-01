<?php
require dirname(__FILE__) . "/consts.php";
require dirname(__FILE__) . "/module.php";
class	App {

	private static $instance = null;

	const LOG_LEV_ERROR = 1;

	private $io = array();

	private $io_wrap;
	private $io_recorder_file;
	const IO_WRAP_RECORD = 1;
	const IO_WRAP_PROXY = 2;

	private $errmsg = null;

	private $mod_root = null;

	public function __set($name, $value) {
		$this->io[$name] = $value;
	}

	public function __get($name) {
		$io = $this->io[$name];
		switch ($this->io_wrap) {
			case self::IO_WRAP_RECORD:
				return new __IoRecorder($io, $this->io_recorder_file, __IoRecorder::RECORD);
			case self::IO_WRAP_PROXY:
				return new __IoRecorder($io, $this->io_recorder_file, __IoRecorder::PROXY);
			default:
				return $io;
		}
	}

	public function __isset($name) {
		return isset($this->io[$name]);
	}

	public function set_module_root($root) {
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
				echo $msg . "\n";
				break;
			default :
				/* do nothing */
				break;
		}
	}

	public function call() {
		$this->io_wrap = null;
		$args = func_get_args();
		if (!$args) {
			return $this->_error("call() func must have at least 1 param");
		}
		$path = array_shift($args);
		return $this->_call_mod($path, $args);
	}

	public function record() {
		$this->io_wrap = self::IO_WRAP_RECORD;
		$args = func_get_args();
		if (count($args) < 2) {
			return $this->_error("record() func must have at least 2 param");
		}
		$path = array_shift($args);
		$this->io_recorder_file = array_pop($args);
		return $this->_call_mod($path, $args);
	}

	public function test() {
		$this->io_wrap = self::IO_WRAP_PROXY;
		$args = func_get_args();
		if (count($args) < 2) {
			return $this->_error("test() func must have at least 2 param");
		}
		$path = array_shift($args);
		$this->io_recorder_file = array_pop($args);
		return $this->_call_mod($path, $args);
	}

	private function _call_mod($path, $args) {
		try {
			$mod = new Module($path, $this->mod_root);
			$result = call_user_func_array(array($mod, "call"), $args);
			if (false === $result) {
				return $this->_error($mod->error());
			} else {
				return $result;
			}
		} catch (Exception $e) {
			return $this->_error("_call_mod() func failed: " . $e->getMessage());
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


class	__IoRecorder {
	private $io;
	private $data_file;
	private $operation;

	private $hash_keys = array();
	private $hash_keys_loaded = false;

	private $fp = null;
	private $clean = false;

	private $errmsg;

	const	PROXY = 2;
	const	RECORD = 3;

	const	CHUNK_KEY_BYTES = 32;
	const	CHUNK_SIZE_BYTES = 16;

	public function __construct($io, $data_file, $operation) {
		if ($operation != self::PROXY && $operation != self::RECORD) {
			throw new Exception("unknown operation: $operation");
		}
		$this->io = $io;
		$this->data_file = $data_file;
		$this->operation = $operation;
	}

	private function _error($errmsg) {
		$this->errmsg = $errmsg;
		throw new Exception($this->errmsg);
		return false;
	}
	
	public function __call($name, $params) {
		if (!$class = get_class($this->io)) {
			throw new Exception("call $name() on a none-object variable");
		}
		if (!method_exists($this->io, $name)) {
			throw new Exception("wrong method {$method}() for class $class");
		}
		switch ($this->operation) {

			case self::PROXY:
				if (!$this->hash_keys_loaded) {
					if (!$this->fp) {
						if (!file_exists($this->data_file) || !($fp = fopen($this->data_file, "r+"))) {
							return $this->_error("wrong data file {$this->data_file}");
						}
						$this->fp = $fp;
					}
					$pos = 0;
					for ( ; ; ) {
						$chunk = fread($this->fp, self::CHUNK_KEY_BYTES + self::CHUNK_SIZE_BYTES);
						if (strlen($chunk) > 0 && strlen($chunk) < self::CHUNK_KEY_BYTES + self::CHUNK_SIZE_BYTES) {
							return $this->_error("broken data file {$this->data_file}");
						} else if (strlen($chunk) == 0){
							break;
						}
						$chunk_size = substr($chunk, 0, self::CHUNK_SIZE_BYTES);
						$chunk_key = substr($chunk, self::CHUNK_SIZE_BYTES, self::CHUNK_KEY_BYTES);
						$this->hash_keys[$chunk_key] = array("size" => $chunk_size - self::CHUNK_KEY_BYTES - self::CHUNK_SIZE_BYTES, "pos" => $pos + self::CHUNK_SIZE_BYTES + self::CHUNK_KEY_BYTES);
						$pos += $chunk_size;
						if (fseek($this->fp, $chunk_size, SEEK_CUR) < 0) {
							return $this->_error("failed to seek file {$this->data_file} at [".($pos + $chunk_size)."]");
						}
					}
					rewind($this->fp);
				}
				$this->hash_keys_loaded = true;
				$hash_key = md5($name . serialize($params));
				if (isset($this->hash_keys[$hash_key])) {
					$c = $this->hash_keys[$hash_key];
					if (fseek($this->fp, $c['pos']) < 0) {
						return $this->_error("failed to seek file {$this->data_file} at [{$c['pos']}]");
					}
					if (($data = fread($this->fp, $c['size'])) === false) {
						return $this->_error("failed to read file {$this->data_file} at [{$c['pos']}] of size [{$c['size']}]");
					}
					if ($data) {
						$data = unserialize($data);
					}
					return $data;
				} else {
					return $this->_error("key {$hash_key} not found ");
				}
				break;


			case self::RECORD:
				if (!$this->clean && file_exists($this->data_file)) {
					if (!unlink($this->data_file)) {
						return $this->_error("failed to clean file {$this->data_file}");
					}
					$this->clean = true;
				}
				$key = md5($name . serialize($params));
				$return = call_user_func_array(array($this->io, $name), $params);
				$data = serialize($return);
				$size = (string)(strlen($data) + self::CHUNK_SIZE_BYTES + self::CHUNK_KEY_BYTES);
				for ($i = strlen($size); $i < self::CHUNK_SIZE_BYTES; $i++) {
					$size = "0" . $size;
				}
				file_put_contents($this->data_file, $size . $key . $data);
				return $return;
				break;


			default:
				return $this->_error("wrong operation {$this->operation}");
				break;
		}
	}

	public function __destruct() {
		if ($this->fp) {
			fclose($this->fp);
		}
	}

}



class	__IoRecorder2 {
	private $io;
	private $data_file;
	private $operation;

	private $hash_keys = array();
	private $hash_keys_loaded = false;

	private $fp = null;

	private $clean = false;

	private $errmsg;

	const	PROXY = 2;
	const	RECORD = 3;

	const	CHUNK_KEY_BYTES = 32;
	const	CHUNK_SIZE_BYTES = 16;

	public function __construct($io, $data_file, $operation) {
		if ($operation != self::PROXY && $operation != self::RECORD) {
			throw new Exception("unknown operation: $operation");
		}
		$this->io = $io;
		$this->data_file = $data_file;
		$this->operation = $operation;
	}

	private function _error($errmsg) {
		$this->errmsg = $errmsg;
		throw new Exception($this->errmsg);
		return false;
	}
	
	public function __call($name, $params) {
		if (!$class = get_class($this->io)) {
			throw new Exception("call $name() on a none-object variable");
		}
		if (!method_exists($this->io, $name)) {
			throw new Exception("wrong method {$method}() for class $class");
		}
		switch ($this->operation) {

			case self::PROXY:
				if (!$this->hash_keys_loaded) {
					if (!$this->fp) {
						if (!file_exists($this->data_file) || !($fp = fopen($this->data_file, "r+"))) {
							return $this->_error("wrong data file {$this->data_file}");
						}
						$this->fp = $fp;
					}
					$pos = 0;
					for ( ; ; ) {
						$chunk = fread($this->fp, self::CHUNK_KEY_BYTES + self::CHUNK_SIZE_BYTES);
						if (strlen($chunk) > 0 && strlen($chunk) < self::CHUNK_KEY_BYTES + self::CHUNK_SIZE_BYTES) {
							return $this->_error("broken data file {$this->data_file}");
						} else if (strlen($chunk) == 0){
							break;
						}
						$chunk_size = substr($chunk, 0, self::CHUNK_SIZE_BYTES);
						$chunk_key = substr($chunk, self::CHUNK_SIZE_BYTES, self::CHUNK_KEY_BYTES);
						$this->hash_keys[$chunk_key] = array("size" => $chunk_size - self::CHUNK_KEY_BYTES - self::CHUNK_SIZE_BYTES, "pos" => $pos + self::CHUNK_SIZE_BYTES + self::CHUNK_KEY_BYTES);
						$pos += $chunk_size;
						if (fseek($this->fp, $chunk_size, SEEK_CUR) < 0) {
							return $this->_error("failed to seek file {$this->data_file} at [".($pos + $chunk_size)."]");
						}
					}
					rewind($this->fp);
				}
				$this->hash_keys_loaded = true;
				$hash_key = md5($name . serialize($params));
				if (isset($this->hash_keys[$hash_key])) {
					$c = $this->hash_keys[$hash_key];
					if (fseek($this->fp, $c['pos']) < 0) {
						return $this->_error("failed to seek file {$this->data_file} at [{$c['pos']}]");
					}
					if (($data = fread($this->fp, $c['size'])) === false) {
						return $this->_error("failed to read file {$this->data_file} at [{$c['pos']}] of size [{$c['size']}]");
					}
					if ($data) {
						$data = unserialize($data);
					}
					return $data;
				} else {
					return $this->_error("key {$hash_key} not found ");
				}
				break;


			case self::RECORD:
				if (!$this->clean && file_exists($this->data_file)) {
					if (!unlink($this->data_file)) {
						return $this->_error("failed to clean file {$this->data_file}");
					}
					$this->clean = true;
				}
				$key = md5($name . serialize($params));
				$return = call_user_func_array(array($this->io, $name), $params);
				$data = serialize($return);
				$size = (string)(strlen($data) + self::CHUNK_SIZE_BYTES + self::CHUNK_KEY_BYTES);
				for ($i = strlen($size); $i < self::CHUNK_SIZE_BYTES; $i++) {
					$size = "0" . $size;
				}
				file_put_contents($this->data_file, $size . $key . $data);
				return $return;
				break;


			default:
				return $this->_error("wrong operation {$this->operation}");
				break;
		}
	}

	public function __destruct() {
		if ($this->fp) {
			fclose($this->fp);
		}
	}

}


