<?php

class A{};
class B extends A{};
$a = new A;
$b = new B;
$c = "abc";

$m = new TestModule("modules/ma/run(a, b, c)");
$m->filter("a", "instanceof(A)");
$m->filter("b", "is_subclass_of(A)");
$m->filter("c", "len()>1");

$m->check($a)->equal(true);
$m->check($b)->equal(true);
$m->check($c)->equal(true);

$m->assert(true)->check($a);




class TestModule {
	private $module ; 
	
	public function __call() {
	}

	public function equal() {
	}
}

