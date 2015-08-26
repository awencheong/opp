<?php
	class A {
		public function count($i) {
			return $i * 2;
		}

		public function _print() {
			return "succ";
		}

		public function check() {
			return array("a"=>array("3"), "b"=>"2", "c"=>null);
		}

		public function test() {
			return array(0, 1, 2, 3);
		}
	}

	class B {
		public function __call($name, $args) {
			return 1;
		}
	}

	require "tester.php";

	$a = new Tester(new A);
	$i = 2;
		$a->count($i)->_should_return($i * 2)
		  ->_print()->_should_return("succ")
		  ->check()->_should_return(array("b"=>2, "a"=>array("3")))
		  ->test()->_should_return(array(0,2,3,1))
		  ->test()->_should_return(array(0,1,2,3));

	$b = new Tester(new B);
	$b->run()->_should_return(1);

