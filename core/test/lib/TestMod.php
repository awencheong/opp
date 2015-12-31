<?php
namespace	app\test\lib;
use	app\Mod;
use	app\Tester;

class	TestMod
{
	public function Test()
	{
		// 1. class
		// 2. func
		// 3. root + class
		// 4. root + func
		$m = new Mod;
		$res = (json_decode($m->call("\\app\\test\\lib\\ModData\\read > \\app\\json"), true));
		Tester::assert($res[0] === 1);

		$m->setModuleRoot("app\\test");
		$res = (json_decode($m->call("lib\\ModData\\read > \\app\\json"), true));
		Tester::assert($res[0] === 1);

		$m->setModuleRoot("app");
		$res = (json_decode($m->call("test\\lib\\ModData\\read > json"), true));
		Tester::assert($res[0] === 1);

		$m->setModuleRoot("app");
		$res = $m->call("test\\lib\\ModData\\out", array("name"=>123));
		Tester::assert($res[0] === 123);

	}

	public function read()
	{
		Tester::assert(false, "should not be triggered");
	}
}

class ModData
{
	public function read()
	{
		return array(1,2,3);
	}

	public function out($name)
	{
		return array($name);
	}
}
