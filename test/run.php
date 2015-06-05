<?php
//require "../core/module.php";
require "../core/test.php";
require "../core/consts.php";

class A{};
class B extends A{};
$a = new A;
$b = new B;

app()->db0 = new mysql();

app()->db0->insert("insert ...");

app()->set_module_root(APP_ROOT . "/modules");
app()->call("ma/run", $a, $b, "here we ");
app()->record("/tmp/ma.run", "ma/run", $a, $b, "here we");
app()->test("/tmp/ma.run", $a, $b, "here we")->should_return(array("awen", "12"));

$r = new __IoRecorder($io, "/tmp/ma.run");
$r->__call($method, $params);


$r = new __TestCaseResult();
$r->should_return($result);
