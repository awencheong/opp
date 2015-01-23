<?php

//加载框架
/*  放置 宏定义 */
require './opp/opp.php';
define('VSTAR_ROOT', OPP_ROOT . "/../");

define('VSTAR_VIEWS_ROOT', VSTAR_ROOT . "/views/");

/* 加载基础设施配置 */

//设置路由
opp()->router = new WebRouter();	// router::get_path() 返回字符串,  router::get_params() 返回数组,  router::get_format() 返回字符串

//设置db
opp()->my_db = new MysqlPdo("127.0.0.1", 3306, "root", "555555", "game");

//设置业务调用者
opp()->module = new ModuleCaller("module", VSTAR_ROOT . "/module");	

//设置默认错误处理
opp()->exception = new ModuleCaller("module", VSTAR_ROOT . "/module", "/error/exception");

//设置插件调用者
opp()->plugin = new PluginCaller("plugin", OPP_ROOT . "/plugin");	// caller::call($params); caller::set_path($path);

//设置游戏常量
define('VSTAR_GAME_NAME',substr(opp()->router->get_host(), 0, strpos(opp()->router->get_host(), ".", 0)));	//取 域名的最后一级，比如  xym.okgame.com 的 xym

//设置设备类型常量
if ($user_agent = opp()->router->get_header("User-Agent")) {
	define('VSTAR_DEVICE', 'mobile');
} else {
	define('VSTAR_DEVICE', 'pc');
}

//设置渲染器
switch (opp()->router->get_format()) {		// render::render($data);
case 'json' :
	opp()->render = new JsonRender();
	break;

case 'xml':
	opp()->render = new XmlRender();
	break;

case 'html':
	include "./smarty/libs/Smarty.class.php";
	$smarty = new Smarty();

	//根据所属的 游戏，设备类型 来决定html模板的根目录
	$smarty->template_dir = VSTAR_VIEWS_ROOT ."/".  VSTAR_GAME_NAME ."/". VSTAR_DEVICE . "/html/";
	$smarty->compile_dir = OPP_ROOT . "/smarty/demo/templates_c/";
	$smarty->config_dir = OPP_ROOT . "/smarty/demo/configs/";
	$smarty->cache_dir = OPP_ROOT . "/smarty/demo/cache/";
	$smarty->left_delimiter = "<{";
	$smarty->right_delimiter = "}>";
	$smarty->caching = false; 

	opp()->render = new HtmlRender($smarty, trim(opp()->router->get_path() . ".html", "/"));
	break;

default :
	opp()->render = new JsonRender();
	break;
}
