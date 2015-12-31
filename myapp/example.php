<?php
	require __DIR__ . "/consts.php";
	require APP_ROOT . "/../core/app.php";
	app()->config(require(APP_ROOT . "/config.php"));
	app()->web->route(require(APP_ROOT . "/rule.php"));
	echo app()->web->render();

	app()->web->route("/user/login/json", ["\\myapp\\modules\\user\\login | \\APP\\json"]);
	app()->web->route("/user/info/modify", ["\\myapp\\modules\\user\\login", "\\myapp\\modules\\user\\info\\modify > \\APP\\json"]);	//按顺序执行每个module, 返回最后一个module的值

	app()->web->path = "/article/get.php";
	app()->web->method = "GET";
	app()->web->get = array("type"=>"note","limit"=>10);
	echo app()->web->render();

	app()->web->path = "/article/put.php";
	app()->web->method = "POST";
	app()->web->post = array("type"=>"note","contents"=>"@/tmp/articles");
	echo app()->web->html();
	echo app()->web->json();
	print_r(app()->web->run());

	app()->cmd->run('/myapp/module/article/get', array("--type"=>"note", "--limit"=>10));
	app()->mod->setBaseNamespace("\\myapp\\modules");
	app()->mod->run('\\article\\get', array("type"=>"note", "limit"=>10));
