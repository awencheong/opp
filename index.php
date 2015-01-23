<?php
require "./config.php";
$params = opp()->router->get_params();
$result = opp()->module->call(opp()->router->get_path(), $params);
$result = opp()->render->get_result($result);
echo $result;


