<?php

//
// const.php  应该放在目录 "OPP_ROOT/core/" 下
//
if (!defined("OPP_ROOT")) {
	define("OPP_ROOT", dirname(dirname(__FILE__)));
}

//
//	默认是否打开监控
//	(默认是不打开监控的)
//
if (!define("APP_MON_DEFAULT")) {
	define("APP_MON_DEFAULT", 0);
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


