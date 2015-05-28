<?php
require "./module.php";
$m = new Module("/ma/run(a, b, c)");
$m->filter("a", "int")->filter("b", "str")->filter("c", "len()>10");
$res = $m->call(1, "awen", "here we ");
if ($m->errno()) {
	echo $m->error()."\n";
} else {
	echo "succ:$res\n";
}
