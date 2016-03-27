<?php
namespace app;

class Mod
{
	private $type = '';
	private $handle = null;
	private $params = array();
	private $options = array(
		'param_from_std' => -1,
		'param_auto_files' => true,
		'param_cache_files' => true,
		'param_map' => false,
	);

	private static $file_contents = array();

	public static $SAFE_MODE = true;

	public static $mods = array();

	public static $baseNameSpace = '/';

	private function _fetchRealModPath($modPath)
	{
		$modPath = str_replace("/", "\\", $modPath);
		if (strpos($modPath, "\\") !== 0) {
			$namespace = self::$baseNameSpace;
			$baseNameSpace = "/" . trim($namespace, "/");
			if ($baseNameSpace == "/") {
				$baseNameSpace = "";
			}
			$baseNameSpace = str_replace("/", "\\", $baseNameSpace);
			$modPath = $baseNameSpace . "\\" . trim($modPath, "\\");
		}
		return $modPath;
	}

	private function _isCustomDefined($modPath, &$handle)
	{
		$modPath = trim($modPath);
		if (!self::$SAFE_MODE && preg_match('/^function\s*[^()]*(\([^()]*\)\s*\{.*\})[;\s]*$/', $modPath, $match)) {
			$modPath = "\$handle = function {$match[1]};";
			eval($modPath);
			return true;
		} else {
			return false;
		}
	}

	public function __construct($mod, $params=array(), $options=array()) {

		if ($this->_isCustomDefined($mod, $handle)) {
			$this->handle = $handle;
			$this->type = 'func';
		} else {
			$cl = $this->_buildFunc($this->_fetchRealModPath($mod));
			$this->handle = $cl['handle'];
			$this->type = $cl['type'];
		}

		foreach ($options as $op => $val) {
			if (isset($this->options[$op])) {
				$this->options[$op] = $val;
			}
		}

		if ($this->options['param_map']) {
			$this->params = $this->_remapParams($params);
		} else {
			$this->params = $params;
		}
	}


	/*
	 * @param	$cmds
	 * 		[
	 * 		{"mod"=>xx, "params"=>xx, "options"=>xx}
	 * 		]
	 */
	public static function initSequence(array $cmds)
	{
		self::$mods = array();
		foreach ($cmds as $c) {
			if (!isset($c['params']) || !is_array($c['params'])) {
				throw new \Exception("wrong params, should be an array");
			}
			if (!isset($c['options']) || !is_array($c['options'])) {
				throw new \Exception("wrong options, should be an array");
			}
			self::$mods[] = new self($c['mod'], $c['params'], $c['options']);
		}
	}

	public static function callSequence($stdin)
	{
		foreach (self::$mods as $m) {
			$stdin = $m->call($stdin);
		}
		return $stdin;
	}

	public function call($stdin)
	{
		$params = $this->_autoReadFiles();

		if ($this->options['param_from_std'] >= 0) {
			$params[$this->options['param_from_std']] = $stdin;
		}

		return call_user_func_array($this->handle, $params);
	}


	private function _autoReadFiles()
	{
		$params = $this->params;
		foreach ($params as $i => $p) {
			if (!$this->options['param_auto_files'] || !is_string($p) || !preg_match('/^@(.*)/', $p, $match)) {
				continue;
			}

			$filename = $match[1];
			if (!file_exists($filename) || is_readable($filename)) {
				$params[$i] = '';
				continue;
			}

			if (isset(self::$file_contents[$filename])) {
				$param[$i] = &self::$file_contents[$filename];
				continue;
			} 

			$str = file_get_contents($filename);
			if (preg_match('/\.json$/', $filename)) {
				$p = json_decode($str, true);
			} else {
				$p = $str;
			}

			if ($this->options['param_cache_files']) {
				self::$file_contents[$filename] = $p;
			}
			$params[$i] = $p;
		}
		return $params;
	}


	private function _remapParams($params)
	{
		$tmp = $params;
		$params = array();
		if ($this->type == 'func') {
			//1. function
			$ref = new \ReflectionFunction($this->handle);
		} else {
			//2. class method
			$ref = new \ReflectionMethod($this->handle[0], $this->handle[1]);
		}
		foreach ($ref->getParameters() as $pm) {
			$pname = $pm->getName();
			if (isset($tmp[$pname])) {
				$params[] = $tmp[$pname];
			} else {
				$params[] = null;
			}
		}
		return $params;
	}

	private function _buildFunc($modPath)
	{
		//1. find function
		//2. find class::method
		//3. throw exception

		if (function_exists($modPath)) {
			return array(
				'handle' => $modPath,
				'type' => 'func'
			);
		}

		$split = strrpos($modPath, "\\");
		$class = substr($modPath, 0, $split);
		$method = substr($modPath, $split + 1);

		if (!$method) {
			throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
		}
		if (!class_exists($class)) {
			throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
		}
		if (!method_exists($class, $method)) {
			throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
		}
		return array(
			'handle' => array(
				new $class(),
				$method
			),
			'type' => 'method'
		);
	}
}

