<?php
namespace	app;
class Tester
{
	private static $errmsg = '';
	public static function lastError() 
	{
		return self::$errmsg;
	}


	private static $succAssertNum = 0;
	private static $lastTrace = null;
	public static function assert($expression, $errmsg = null)
	{
		$trace = debug_backtrace();
		self::$lastTrace = $trace[0];
		if ($expression) {
			self::$succAssertNum += 1;
		} else {
			throw new \Exception($errmsg);
		}
	}

	public static function auto($dir, $namespace)
	{
		foreach (scandir($dir) as $file) {
			if ($file == "." || $file == "..") {
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if (is_dir($path)) {
				self::auto($path, $namespace . "\\" . $file );

			} else if (preg_match('/(^.*Test[\w-_\d]+)\.php$/', $file) && $file != "Tester.php") {
				$base = substr($file, 0, strrpos($file, "."));
				$class = $namespace . "\\" . $base;
				if (!self::test($class)) {
					return false;
				}
			}
		}
		echo "dir[$dir] Finished!\n";
		return true;
	}

	private static function outputError($class)
	{
		echo "class $class failed!\n" ;
		echo "\tsuccess\t[".self::$succAssertNum."] assertions\n" ;
		echo "\tfailed at	file: ".self::$lastTrace['file']."[".self::$lastTrace['line']."]\n";
		if (self::$errmsg) {
			echo "\terror: " . self::$errmsg . "\n";
		}
		echo "\n";
		self::$succAssertNum = 0;
		self::$lastTrace = null;
	}

	private static function outputSucc($class)
	{
		echo "class $class success!\n" ;
		echo "\tsuccess\t[".self::$succAssertNum."] assertions\n\n" ;
		self::$succAssertNum = 0;
		self::$lastTrace = null;
	}

	public static function test($class)
	{
		try {
			$ref = new \ReflectionClass($class);
			foreach ($ref->getMethods() as $m) {
				$methodName = $m->getName();
				if (preg_match('/Test.*/', $methodName)) {
					$m->invoke(new $class);
					self::outputSucc($class . "::" . $methodName. "()");
				}
			}
			return true;
		} catch (\Exception $e) {
			self::outputError($class);
			self::$errmsg = $e->getMessage();
			return false;
		}
	}
}
