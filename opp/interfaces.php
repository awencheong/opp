<?php

function opp() {
}

function opp_assert($assert){
}

class OppException extends Exception{
}

class	Opp {
	const	TYPE_GLOBAL = 0;	//Object type "Normal"
	const	TYPE_DB	=	1;	//Object type "DB"
	const	TYPE_MODULE =	2;

	private $globals = array();
	private $dbs = array();
	private $modules = array();

	public function __get($name){}

	public function load($name, $object, $type = Opp::TYPE_GLOBAL){}

	public function delete($name){}

	public function replace($name, $object){}

}


interface	Http	{

	public function path();

	public function get();

	public function request();

	public function post();

	public function files();

	public function headers();

	public function body();

}


interface	Net		{

	public function host();

	public function port();

	public function client_ip();

	public function client_port();
}


interface	Request extends	Http, Net {
}



interface	Caller	{
	public function call($path, $params);
}

interface	Module {
	public function run($params);
}

/*  config.php
opp()->app = new \Opp\Caller\Obj();
opp()->app->baseDir = "/path/to/dir";


opp()->load("html", "/modules/render/html");		//这是个caller 型的插件
opp()->html->globalVars = array();
opp()->html->baseDir = "/path/to/html";
opp()->html->baseSmarty = "/path/to/smarty";


opp()->load("json", "/modules/render/json");		//这是个run 型的插件


*/


/*	run_html.php

$result = opp()->app->call($path, $params);
echo opp()->html->call($path, $params);

*/

/*  run_json.php

$result = opp()->app->call($path, $params);
echo opp()->json->run($params);

*/


/*  从post中获取文件，并存储到指定位置上去 
config:
opp()->load("upload", new \Opp\Module\Upload);
opp()->upload->baseDir = "/path/to/upload";
 
run:
$result = opp()->upload->run(opp()->http->files());
echo opp()->json->run($result);


$result = opp()->upload(opp()->http->files());
echo opp()->json($result);
*/


/*	获取验证码
config:
opp()->load("codebar",  "/app/codebar");

run:
$img = opp()->codebar->run();
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
