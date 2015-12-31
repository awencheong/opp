<?php
namespace	app\test;
use	app\Tester;
class	TestApp
{
	public function TestRegister()
	{
		app()->register(__DIR__, "\\just\\test");
		$t = new \just\test\AppData;
		Tester::assert($t->debug() === 12);

		app()->register(__DIR__, "just\\debug");
		$t = new \just\debug\AppData2;
		Tester::assert($t->debug() === 12);
	}

}
