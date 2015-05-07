<?php
namespace	app;

if (!defined("OPP_LOADED")) {
	define("OPP_TIME_ZONE", "Asia/Hong_Kong");
	date_default_timezone_set(OPP_TIME_ZONE);
	define("OPP_LOADED", true);

	// App Root Path
	// 
	//
	define("OPP_ROOT", dirname(realpath(__FILE__)));

	// App AutoLoad:
	//
	// basedir:	APP_ROOT
	// "\app\db\mysql_pdo"	will map to  file: APP_ROOT . "/db/mysql_pdo.php"
	//
	// *	class name not started with "app" will be ignored
	// *	once the class is not found in this autoloader, exception will be thrown out to terminate the program
	//
	spl_autoload_register(function($class_name) {
		if (strpos($class_name, "app") === 0) {
			$file_path = OPP_ROOT . "/".str_replace("\\", "/", substr($class_name, 4)).".php";
			if (file_exists($file_path)) {
				require($file_path);
				app_assert(class_exists($class_name), "class {$class_name} not found from {$file_path}", APP_ERR_CLASSNOTFOUND);
			}
		}
	});

	set_exception_handler(function($e) {
		echo "[ERROR]" . $e->getMessage() . "\n";
	});

	// App Errors:
	//
	// APP_ERR_SYSTEM		100
	// APP_ERR_CLASSNOTFOUND	101
	//
	define('APP_ERR_SYSTEM' , 100);
	define('APP_ERR_CLASSNOTFOUND' , 101);

	// APP Get Current Instance
	//
	//	function app(), short for  App::curr()
	//
	function app() {
		return App::curr();
	}

	function app_assert($assert, $errmsg, $errno = APP_ERR_SYSTEM){
		if (!$assert) {
			throw new AppException($errmsg, $errno);
		}
	}

	class AppException extends \Exception{
	}

	class	App {
		const	TYPE_DB	=	1;	//Object type "DB"
		const	TYPE_MODULE =	2;
		const	TYPE_TOOL =	3;

		private static $curr_app = null;

		public function curr() {
			if (!self::$curr_app) {
				self::$curr_app = new self;
			}
			return self::$curr_app;
		}

		/* 
		 * only load object which implements interface IDb
		 *
		 */
		private $dbs = array();

		/* 
		 * only load object which implements interface IModule 
		 *
		 */
		private $modules = array();

		/* 
		 * only load object which implements interface ITools
		 */
		private $tools = array();

		private $indices = array();

		public function __get($name){
			switch($this->indices[$name]) {
			case	self::TYPE_MODULE:
				$list = &$this->modules;
				break;

			case	self::TYPE_DB:
				$list = &$this->dbs;
				break;

			case	self::TYPE_TOOL:
				$list = &$this->tools;
				break;

			default	:
				//app_assert(false, "unknown object type of object [$name]");
				return false;
			}
			return $list[$name];
		}

		public function load($name, $object, $type = App::TYPE_MODULE){
			app_assert($name != null, "object name should not be null");
			switch ($type) {
			case	self::TYPE_MODULE:
				app_assert($this->modules[$name] == null, "object [$name] already exists");
				app_assert($object instanceof IModule, "obj [{$name}] should be instance of IModule");
				$this->modules[$name] = $object;
				break;

			case	self::TYPE_DB:
				app_assert($this->dbs[$name] == null, "object [$name] already exists");
				app_assert($object instanceof IDb, "obj [{$name}] should be instance of IDb");
				$this->dbs[$name] = $object;
				break;

			case	self::TYPE_TOOL:
				app_assert($this->tools[$name] == null, "object [$name] already exists");
				app_assert($object instanceof ITool, "obj [{$name}] should be instance of ITool");
				$this->tools[$name] = $object;
				break;

			default	:
				app_assert(false, "unknown object type [{$type}]");

			}
			$this->indices[$name] = $type;
		}

		public function delete($name){
			switch($this->indices[$name]) {
			case	self::TYPE_MODULE:
				$list = &$this->modules;
				break;

			case	self::TYPE_DB:
				$list = &$this->dbs;
				break;

			case	self::TYPE_TOOL:
				$list = &$this->tools;
				break;

			default	:
				app_assert(false, "unknown object type [$type]");
			}
			unset($list[$name]);
			unset($this->indices[$name]);
		}

		public function replace($name, $object){
			switch($this->indices[$name]) {
			case	self::TYPE_MODULE:
				$list = &$this->modules;
				app_assert($object instanceof IModule, "obj [{$name}] should be instance of IModule");
				break;

			case	self::TYPE_DB:
				$list = &$this->dbs;
				app_assert($object instanceof IDb, "obj [{$name}] should be instance of IDb");
				break;

			case	self::TYPE_TOOL:
				$list = &$this->tools;
				app_assert($object instanceof ITool, "obj [{$name}] should be instance of ITool");
				break;

			default	:
				app_assert(false, "unknown object type [$type]");
			}
			$list[$name] = $object;
		}

	}

	interface	IDb {}

	interface	ITools {}

	interface	IModule {
		public function run($params);
	}
}

/*  config.php
app()->app = new \App\Caller\Obj();
app()->app->baseDir = "/path/to/dir";


app()->load("html", "/modules/render/html");		//这是个caller 型的插件
app()->html->globalVars = array();
app()->html->baseDir = "/path/to/html";
app()->html->baseSmarty = "/path/to/smarty";


app()->load("json", "/modules/render/json");		//这是个run 型的插件


 */


/*	run_html.php

$result = app()->app->call($path, $params);
echo app()->html->call($path, $params);

 */

/*  run_json.php

$result = app()->app->call($path, $params);
echo app()->json->run($params);

 */


/*  从post中获取文件，并存储到指定位置上去 
config:
app()->load("upload", new \App\Module\Upload);
app()->upload->baseDir = "/path/to/upload";

run:
$result = app()->upload->run(app()->http->files());
echo app()->json->run($result);


$result = app()->upload(app()->http->files());
echo app()->json($result);
 */


/*	获取验证码
config:
app()->load("codebar",  "/app/codebar");

run:
$img = app()->codebar->run();
$code = $img['code'];
echo $img['img'];

 */




/*
 *	教程应该包含:
 *
 *		1.  一个博客网站
 *		2.	一个电商网站
 *		3.	一个后台服务群
 *		4.	一个游戏 Open API
 *
 */
