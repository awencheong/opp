<?php
namespace	app;

function app()
{
	return App::instance();
}

class	AutoLoad
{
	private $dir = "";
	private $baseNamespace = "";
	public function _load($class) 
	{
		$dir = $this->dir;
		$preSpacename = $this->baseNamespace;
		$class = trim($class, "\\");
		if (($pos = strpos($class, $preSpacename)) === 0) {
			$file = $dir . DIRECTORY_SEPARATOR .
				str_replace("\\", DIRECTORY_SEPARATOR, substr($class, strlen($preSpacename))) . ".php";
			if (file_exists($file)) {
				require $file;
			}
		}
	}

	public static function register($dir, $baseNamespace)
	{
		$obj = new self;
		$obj->dir = $dir;
		$obj->baseNamespace = trim($baseNamespace, "\\");
		spl_autoload_register(array($obj, "_load"));
	}
}

AutoLoad::register(__DIR__ . "/test", "app\\test");
AutoLoad::register(__DIR__ . "/lib", "app");

use	app\Web;
use	app\Cmd;
use	app\Mod;

final	class	App
{

	private $web;
	private $cmd;
	private $mod;
	private function _init()
	{
		$this->web = new Web;
		$this->cmd = new Cmd;
		$this->mod = new Mod;
	}
	private function __construct()
	{}

	private static $ini = array();
	public static function instance($name = "APP")
	{
		if (!isset(self::$ini[$name])) {
			self::$ini[$name] = new self();
			self::$ini[$name]->_init();
		}
		return self::$ini[$name];
	}

	public function register($dir, $basename)
	{
		AutoLoad::register($dir, $basename);
	}


	private $io = array();
	public function __get($name)
	{
		if (isset($this->$name)) {
			return $this->$name;
		}
		if (!isset($this->io[$name])) {
			return false;
		}
		return $this->io[$name];
	}

	public function __set($name, $obj) 
	{
		return false;
	}

	public function __isset($name) 
	{
		return isset($this->io[$name]);
	}

	public function config(array $cfg)
	{
		foreach ($cfg as $name => $obj) {
			$this->io[$name] = $obj;
		}
	}
}
