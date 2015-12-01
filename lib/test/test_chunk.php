<?php

	require "chunk.php";
	require "tester.php";
	$file = "/tmp/ma.run";
	if (file_exists($file)) {
		unlink($file);	//clean file
	}
	$c = new Tester(new ChunkFile($file));

	for ($i = 0; $i < 10; $i ++) {
		$c->push("[$i]im\n")->_should_return(null);	// $c 如果 察觉 $c 实现了 error() 方法， 调用之, 并判断错误情况, 如果发现错误， 终止测试脚本;  每一次调用 push, 都会记录本次的输出， 如果调用_should_return(), 则将当前值跟实际的输出进行判断, 输出" calling push() successfully, 输出错误信息, 终止调试 "
	}

	foreach ($c as $index => $chunk) {
		// 如果 察觉 $c 实现了 Iterator 接口， 输出 " $c implements Iterator , current()/next()/valid() successfully" 或者 "$c current()/next()/valid() 失败, 输出错误信息, 终止调试"
		echo $chunk;
	}


	// 清除调试信息 
	$c->get_pos(0)->_should_return(array('pos'=>0, 'size'=>22));
	$c->get(0)->_should_return("[0]im\n");
	$c->get(9)->_should_return("[9]im\n");
	$c->get(2)->_should_return("[2]im\n");
	$c->get(3)->_should_return("[3]im\n");

	$c->push("[100]im 100\n");
	$c->get(10)->_should_return("[100]im 100\n");
	$c->chunks()->_should_return(array());
