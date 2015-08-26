<?php
require "./func.php";

$c = new Tester("./func.php");
$c->fortest()->_should_return(array("name"=>"awen"));
$c->aa();
