<?php
namespace	app;

if (!defined("OPP_LOADED")) {
	class	App {

		private static $curr_app = null;

		public function curr() {
			if (!self::$curr_app) {
				self::$curr_app = new self;
			}
			return self::$curr_app;
		}

		public function __get($name){
		}

		public function load($name, $object, $type = App::TYPE_MODULE){
		}

		public function delete($name){
		}

		public function replace($name, $object){
		}

	}


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

}
