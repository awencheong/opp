<?php

//
// const.php  应该放在目录 "OPP_ROOT/core/" 下
//
if (!defined("CORE_ROOT")) {
	define("CORE_ROOT", dirname(__FILE__));
}
if (!defined("APP_ROOT")) {
	define("APP_ROOT", CORE_ROOT . "/..");
}

//
//	默认是否打开监控
//	(默认是不打开监控的)
//
if (!defined("APP_MON_DEFAULT")) {
	define("APP_MON_DEFAULT", 0);
}

//	是否记录测试用例
//	（默认不打开用例记录)
if (!defined("APP_RECORD_TEST_CASE")) {
	define("APP_RECORD_TEST_CASE", 0);
}

//
// 	APP_MON_DURATION, 	监控耗时
// 	APP_MON_MEMORY, 	监控内存使用量
// 	APP_MON_COUNT, 		监控调用次数
//
define("APP_MON_DURATION", 1);
define("APP_MON_MEMORY", 2);
define("APP_MON_COUNT", 3);

//
// 	APP_MON_FREQUENCY_COUNT		按照次数比例来采样, 比如  1/50
// 	APP_MON_FREQUENCY_TIME		按照时间比例来采样, 比如  1/50min	
//
define("APP_MON_FREQUENCY_COUNT", 1);
define("APP_MON_FREQUENCY_DURATION", 2);


