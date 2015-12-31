<?php
	require __DIR__ . "/../core/include.php";
	require __DIR__ . "/consts.php";
	app()->register(MYAPP_ROOT, "myapp");
	app()->config(require(MYAPP_ROOT . "/config.php"));
	app()->web->rules(require(MYAPP_ROOT . "/rule.php"));
	app()->web->setModuleRoot("\\myapp\\modules"); 
	app()->web->setTplRoot(PROJECT_ROOT); 
	app()->web->setTplSuffix(".html");
	$res = '';
	switch (app()->web->getPathSuffix()) {
	case ".html":
		$res = app()->web->html(true);
		break;

	case ".json":
	default:
		$res = app()->web->json();
	}
	if (($err = app()->web->lastError())) {
		echo $err;
	} else {
		echo $res;
	}
