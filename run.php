<?php
require "./core/module.php";
class A{};
class B extends A{};
$a = new A;
$b = new B;

$m = new Module("modules/ma/run(a, b, c)");
$m->filter("a", "instanceof(A)")->filter("b", "is_subclass_of(A)")->filter("c", "len()>1");

$res = $m->call($a, $b, "here we ");

if ($m->errno()) {
	echo $m->error()."\n";
} else {
	echo "succ:$res\n";
}
