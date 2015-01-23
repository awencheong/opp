<?php

//加载框架
/*  放置 宏定义 */
require './app.php';
define('VSTAR_ROOT', dirname(realpath(__FILE__)));
define('VSTAR_VIEWS_ROOT', dirname(realpath(__FILE__)) . "/views/" );

/* 加载基础设施配置 */

//设置路由
app()->router = new WebRouter();	// router::get_path() 返回字符串,  router::get_params() 返回数组,  router::get_format() 返回字符串

//设置db
app()->my_db = new MysqlPdo("127.0.0.1", 3306, "root", "555555", "game");

//设置业务调用者
app()->module = new ModuleCaller("module", VSTAR_ROOT . "/module");	

//设置默认错误处理
app()->exception = new ModuleCaller("module", VSTAR_ROOT . "/module", "/error/exception");

//设置插件调用者
app()->plugin = new PluginCaller("plugin", APP_ROOT . "/plugin");	// caller::call($params); caller::set_path($path);

//设置游戏常量
define('VSTAR_GAME_NAME',substr(app()->router->get_host(), 0, strpos(app()->router->get_host(), ".", 0)));	//取 域名的最后一级，比如  xym.okgame.com 的 xym

//设置设备类型常量
if ($user_agent = app()->router->get_header("User-Agent")) {
	define('VSTAR_DEVICE', 'mobile');
} else {
	define('VSTAR_DEVICE', 'pc');
}

//设置渲染器
switch (app()->router->get_format()) {		// render::render($data);
case 'json' :
	app()->render = new JsonRender();
	break;

case 'xml':
	app()->render = new XmlRender();
	break;

case 'html':
	include "./smarty/libs/Smarty.class.php";
	$smarty = new Smarty();

	//根据所属的 游戏，设备类型 来决定html模板的根目录
	$smarty->template_dir = VSTAR_VIEWS_ROOT ."/".  VSTAR_GAME_NAME ."/". VSTAR_DEVICE . "/html/";
	$smarty->compile_dir = "./smarty/demo/templates_c/";
	$smarty->config_dir = "./smarty/demo/configs/";
	$smarty->cache_dir = "./smarty/demo/cache/";
	$smarty->left_delimiter = "<{";
	$smarty->right_delimiter = "}>";
	$smarty->caching = false; 

	app()->render = new HtmlRender($smarty, trim(app()->router->get_path() . ".html", "/"));
	break;

default :
	app()->render = new JsonRender();
	break;
}
