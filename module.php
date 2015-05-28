<?php
/*
 *	module caller,  used like this:
 *		$mod = new Module("/path/of/module($name, $age, $info)", 'object');
 *		$mod->filter("name", "str", "required", '/^abc$/');
 *		$mod->filter("age", "int", "required", '>10', '<200');
 *		$mod->call($p1, $p2, $p3);
 *		$mod->applay(["name"=>$p1, "age"=>$p2, "info"=>$p3]);
 *	
 */
 class	Module {
 		public function errno(){
			return $this->errno;
		}

		public function error() {
			return $this->errmsg;	
		}

 		private $errmsg = '';
		private $errno = 0;	/* 0 means that everything is fine */
		private function _error($errmsg, $errno=1) {
			$this->errmsg = $errmsg;
			$this->errno = $errno;
			return false;
		}

 		/*
		 * @param,	path of module, it's an array of dirs
		 */
 		private $path = array();

		/*
		 * @param	params, [ param_name => ['pos', 'required', 'str', '/^preg$/'] ]
		 */
 		private $params = array();

		private $force_check = false;

 		/*
		 * @param	path, format like : /path/to/module($param1, $param2, ...)
		 */
 		public function __construct($path) {
			if (!preg_match('/\/([\w-_\/]+)(\(\s*[\w-_]+(\s*,\s*[\w-_]+\s*)*\))$/', $path, $match)) {
				return $this->_error("wrong path:{$path}");
			}
			$path = array_filter(explode("/", $match[1]));
			$this->path = array_values($path);

			$params = $match[2];
			$params = trim(strtr($params, array('('=>'', ')'=>'')));
			$params = explode(',', $params);
			
			$i = 0;
			foreach ($params as $p) {
				$this->params[trim($p)] = array("pos"=>$i);
				$i ++;
			}
		}


		public function check($params) {
			foreach ($this->params as $name => $p) {
				if ($this->force_check && !isset($p['rule'])) {
					return $this->_error("`force_check` tag is open, so, your param[{$name}] must have a filter rule");
				}
				if (isset($p['rule'])) {
					$rule = $p['rule'];

					if (isset($rule['required']) && !isset($params[$name])) {
						return $this->_error("param[{$name}] is required");
					}
					unset($rule['required']);

					if (isset($params[$name])) {
						$pm = $params[$name];

						foreach (array_keys($rule) as $r) {
							switch (true) {

								case ($r == 'num' && (!is_numeric($pm))) :
									return $this->_error("param[{$name}] should be a number");

								case ($r == 'int' && (!is_numeric($pm) || intval($pm) != $pm)):
									return $this->_error("param[{$name}] should be an int");

								case ($r == 'str' && !is_string($pm)):
									return $this->_error("param[{$name}] should be a string");

								default: 
									switch (true) {

										case (preg_match('/^\/.+\/$/', $r) && !preg_match($r, $pm)) :	//正则匹配
											return $this->_error("param[{$name}] should be match preg $r");


										case (preg_match('/^len\(\)([<>=]+)([0-9]+)$/i', $r, $len_cmp)) :
											$cmp = $len_cmp[1];
											$len = intval($len_cmp[2]);
											switch (true) {

												case ($cmp == '>' && strlen($pm) <= $len) :
													return $this->_error("param[{$name}] length should be >{$len}");

												case ($cmp == '<' && strlen($pm) >= $len) :
													return $this->_error("param[{$name}] length should be <{$len}");

												case ($cmp == '>=' && strlen($pm) < $len) :
													return $this->_error("param[{$name}] length should be >={$len}");

												case ($cmp == '<=' && strlen($pm) > $len) :
													return $this->_error("param[{$name}] length should be <={$len}");
											}
											break;
									}
							}
						}
					}
				}
			}
			return true;
		}

		public function filter() {
			$args = func_get_args();
			$param = array_shift($args);
			if (!preg_match('/^[\w-_]+$/', $param)) {
				return $this->_error("wrong param name:{$param}");
			}
			if (!isset($this->params[$param])) {
				return $this->_error("param name not exists:{$param}");
			}
			if (!isset($this->params[$param]['rule'])) {
				$this->params[$param]['rule'] = array();
			}
			$rule = &$this->params[$param]['rule'];

			foreach ($args as $a) {
				if (in_array($a, array('required', 'str', 'num', 'int'))) {
					$rule[$a] = 1;
				}
				if (preg_match('/^len\(\)[<>=]+[0-9]+$/', $a) || preg_match('/\/.*\/$/', $a)) {
					$rule[$a] = 1;
				}
			}
		}

		public function call() {
			$params = array();
			foreach (func_get_args() as $i => $p) {
				foreach ($this->params as $name => $pm) {
					if ($pm['pos'] == $i) {
						$params[$name] = $p;
					}
				}
			}
			if (!$this->check($params)) {
				return false;
			}
			$path = $this->path;
			$func = array_pop($path);
			if ($path) {
				$file = implode("/", $path) . ".php";
				if (!file_exists($file)) {
					return $this->_error("file {$file} not found");
				}
				include "$file";
				if (!function_exists($func)) {
					return $this->_error("function {$func}() not found in file {$file}");
				}
			} else {
				if (!function_exists($func)) {
					return $this->_error("function {$func}() not found in module {$path}");
				}
			}
			return call_user_func_array($func, $params);
		}

 }

