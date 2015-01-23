<?php
require "./config.php";
$params = app()->router->get_params();
$result = app()->module->call(app()->router->get_path(), $params);
$result = app()->render->get_result($result);
echo $result;


