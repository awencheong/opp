<?php
namespace app;
require __DIR__ . "/include.php";
if ($argc >= 2) {
	$dir = $argv[1];
} else {
	$dir = __DIR__ . "/test";
}
Tester::auto($dir, "\\app\\test");
//$t->test("\\app\\test\\lib\\TestMod");
