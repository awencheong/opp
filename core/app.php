<?php
/*
 *
example:

app()->deny("*.php", "GET");
app()->only("/admin/show.php", "GET");
app()->allow("/admin/show.php", "POST");	
app()->authenticate("*.php", "/admin/auth");
app()->location("*.php", "/admin/{1}");
app()->location("(admin|manager)/(show|name).php", "/{1}/{2}");
app()->location("(admin|manager)/*.*", "/{1}/{2}");
app()->location("/(admin|manager|gift)\/(.*)\.php$/", "/admin/{2}");
//app()->view("*.php", function($data){return json_encode($data);});
app()->view("/^.*(php|json|jsonp)$/", function($data){return json_encode($data);});
app()->view("/manager/*.php", function($data){ return json_encode(array("manager" => $data)); });

 */
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
	private function _init()
	{
		$this->web = new Web;
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
		if ($this->$name) {
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

	public function authenticate($url, $path)
	{
		$this->web->authenticate($url, $path);
		return $this;
	}

	public function location($url, $path)
	{
		$this->web->location($url, $path);
		return $this;
	}

	public function view($url, $viewer)
	{
		$this->web->view($url, $viewer);
		return $this;
	}

	public function run()
	{
		$this->web->run();
		return $this;
	}
}
