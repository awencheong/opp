<?php
//require "../core/module.php";
require "../core/consts.php";
require "../core/app.php";


class A{};
class B extends A{};
$a = new A;
$b = new B;

class _mysql {
	public function select($sql) {
		return array("here we go");
	}
}

app()->db0 = new _mysql();
app()->set_module_root(APP_ROOT . "/modules");
print_r(app()->call("ma/run(a, b)", $a, $b));
print_r(app()->record("ma/run(a, b)", $a, $b, "/tmp/ma.run"));
print_r(app()->test("ma/run(a, b)", $a, $b, "/tmp/ma.run"));
