<?php
namespace	app;

class	Mod
{
	private $moduleRoot = '';
	public function setModuleRoot($namespace)
	{
		$this->moduleRoot = "\\" . trim($namespace, "\\");
	}

	/*
	 * @param	$module,  
	 * 		1)  user\login\get	
	 * 		2)  user\login\get > \app\json
	 *
	 * @return	throw Exception on error;  or return result on success;
	 */
	public function call($modules, $params = array())
	{
		$modules = explode(">", $modules);
		$res = null;
		$start = true;
		foreach ($modules as $m) {
			if ($start) {
				$res = $this->call_mod(trim($m), $params, 0);
				$start = false;
			} else {
				$res = $this->call_mod(trim($m), $res, 1);
			}
		}
		return $res;
	}

	private function call_mod($module, $input = array(), $pipe = null)
	{
		$func = $this->createMethod($module, $params, $type, $class);
		if ($pipe) {
			$args = array($input);
		} else {
			$args = array();
			foreach ($params as $name) {
				if (isset($input[$name])) {
					$args[$name] = $input[$name];
				} else {
					$args[$name] = null;
				}
			}
		}
		if ($type == "call_method") {
			return $func->invokeArgs(new $class, $args);
		} else if ($type == "call_func") {
			return $func->invokeArgs($args);
		}
	}

	private function createMethod($module, &$params, &$type, &$class)
	{
		$tmp = $module;
		$spl = strrpos($module, "\\");
		$method = substr($module, $spl + 1);
		$class = substr($module, 0, $spl);
		if (strpos($class, "\\") !== 0) {
			$class = $this->moduleRoot . "\\" . $class;
			$module = $this->moduleRoot . "\\" . $module;
		}

		$type = "";
		if (function_exists($module)) {
			$func = new \ReflectionFunction($module);
			$type = "call_func";
		} else if ($class && class_exists($class) && method_exists($class, $method)) {
			$func = new \ReflectionMethod($class, $method);
			$type = "call_method";
		} else {
			throw new \Exception("wrong module[$tmp], no function[$module] or class[$class::$method()] found");
		}
		$params = array();
		foreach ($func->getParameters() as $p) {
			$params[] = $p->name;
		}
		return $func;

	}

}
