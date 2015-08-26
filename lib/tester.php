<?php


class	Tester	{

	protected $obj;
	private $class;
	private $calling_method;

	private $return;
	private $errmsg;

	public function __construct($test_obj) {
		$this->calling_method = "__construct";
		if (is_string($test_obj)) {	
			if (preg_match('/\.php$/', $test_obj)) {	/* a function file */
				$this->class = "Function";
				$test_obj = new FuncCaller($test_obj);
			} else {
				$this->class = '[static] ' . $test_obj;
				$test_obj = new StaticMethodCaller($test_obj);	/* a static class */
			}
			
		} else {
			if (!$this->class = get_class($test_obj)) {
				return $this->_error("wrong variable , it's not an object")->_print();
			}	 
		}
		$this->obj = $test_obj;
	}

	private function _print($msg = null, $tag = null ) {
		if (!$msg) {
			$msg = $this->errmsg;
		}
		$msg = "calling {$this->class}::{$this->calling_method} {$tag} >>>  " . $msg;
		echo $msg . "\n";
		return $this;
	}
	
	private function _error($errmsg) {
		$this->errmsg =  $errmsg;
		return $this;
	}

	private function _args_to_line($args) {
		$line = '';
		foreach ($args as $a) {
			switch (true) {
				case is_string($a):
					if (strlen($a) > 10) {
						$a = substr($a, 0, 10) . " ... ";
					}
					$line .= $a . ",";
					break;

				case is_numeric($a):
					$line .= $a . ",";
					break;

				case null != get_class($a):
					$line .= "Obj(" . get_class($a) . "),";
					break;

				case is_array($a):
					$line .= "Array,";

				default:
					$line .= "Unknown(...),";
			}
		}
		return $line;
	}

	public function __get($name) {
		if (isset($this->obj->$name)) {
			$this->return = $this->obj->$name;
			return $this;
		} else {
			return $this->_print("property '$name' not found");
		}
	}

	public function __call($name, $args) {
		$this->calling_method = $name;
		if ($this->errmsg) {
			return $this->_print();
		}
		if (!is_callable(array($this->obj, $name))) {
			return $this->_error("method $name() not found")->_print();
		} 
		try {
			$this->return = call_user_func_array(array($this->obj, $name), $args);
			if (is_callable(array($this->obj, 'error')) && ($err = $this->obj->error())) {
				return $this->_error("error :{$err}")->_print();
			}
			return $this;

		} catch (Exception $e) {
			return $this->_error("exception :{$e->getMessage()}")->_print();
		}
	}

	public function _should_return($value) {
		if ($this->errmsg) {
			return $this;
		}
		if (is_callable(array($this->obj, 'error')) && ($err = $this->obj->error())) {
			$this->_print("eror message:" . $err, "fail");
			return $this;
		}
		switch (true) {
			case ($value == null): 
				return $this->_equal_null($value);
				break;

			case is_numeric($value):
			case is_string($value):
				return $this->_equal_string($value);
				break;

			case is_array($value):
				return $this->_equal_array($value);
				break;

			case (null != get_class($value)) :
				return $this->_equal_object($value);
				break;

			default:
				return $this->_print("UNKNOWN RETURN TYPE", "fail");
			
		}
	}

	private function _equal_null($value) {
		if (is_array($this->return)) {
			$return = json_encode($this->return);
		}
		if (is_array($value)) {
			$return_value = json_encode($value);
		}
		if ($this->return == $value) {
			return $this->_print($return, "succ");
		} else {
			return $this->_print("the returned value should be ".$return_value.", but got {$this->_fixed_str($return)}", "fail");
		}
	}

	public function _return(){
		return $this->return;
	}

	private function _equal_string($value) {
		if ($this->return == $value) {
			return $this->_print("{$value}", "succ");
		} else {
			return $this->_print("the returned value should be {$value}, but got {$this->return}", "fail");
		}
	}

	private function _fixed_str($str) {
		if (!is_string($str)) {
			$str = (string)$str;
		}
		if (strlen($str) <= 50) {
			return $str;
		} else {
			return substr($str, 0, 50) . " ...";
		}
	}

	private function _equal_array(array $value) {
		if (!is_array($this->return)) {
			return $this->_print("the returned value should be an array, but got ".$this->_fixed_str($this->return), "fail");

		} else if (!$this->_match_array($this->return, $value)) {
			return $this->_print("the returned array should be ".$this->_fixed_str(json_encode($value)).", but got ".$this->_fixed_str(json_encode($this->return)), "fail");

		} else {
			return $this->_print($this->_fixed_str(json_encode($value)), "succ");
		}
	}

	private function _match_array(array $match, array $arr) {
		$keys = array();
		foreach ($arr as $key => $a) {
			if (!isset($match[$key])) {
				return false;
			} else if (is_array($match[$key])){
				if (!is_array($a)) {
					return false;
				}
				if (!$this->_match_array($match[$key], $a)) {
					return false;
				}
			} else {
				if (is_array($a) || $a != $match[$key]) {
					return false;
				}
				$keys[] = $key;
			}
		}
		/* all keys matched */
		return empty(array_diff(array_keys($match), $keys));
	}
}



class 	FuncCaller   {
	private static $included = array();
	private $errmsg = null;
	public function __construct($file_path) {
		if (!self::$included[$file_path]) {
			if (file_exists($file_path)) {
				include_once $file_path;
			} else {
				$this->errmsg = "failed to include file $file_path";
			}
		}
	}

	public function error() {
		return $this->errmsg;
	}

	public function __call($name, $args) {
		if ($this->errmsg) {
			return false;
		}
		if (is_callable($name)) {
			return call_user_func_array($name, $args);
		} else {
			$this->errmsg = "function $name() not found";
			return false;
		}
	}
}

class StaticMethodCaller {
	private $errmsg = null;
	public function error() {
		return $this->errmsg;
	}

	private $class = null;

	public function __construct($class) {
		if (!class_exists($class)) {
			$this->errmsg = "class {$class} not exists";
			return;
		}
		$this->class = $class;
	}

	public function __call($name, $args) {
		if ($this->errmsg) {
			return false;
		}
		if (is_callable(array($this->class, $name))) {
			return call_user_func_array(array($this->class, $name), $args);
		} else {
			$this->errmsg = "static method {$this->class}::{$name} not found";
			return false;
		}
	}
}
