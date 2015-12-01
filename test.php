<?php
	require "app/app.php";

	class	Mysql {
		public function query($sql) {
			
		}
	}
	ini_set("memory_limit", "1024m");

	$time_list = gen_time();
	clean(db(), 'int_time');
	clean(db(), 'str_time');

	gen_int(db(), $time_list);
	gen_str(db(), $time_list);

	search_int(db(), $time_list);
	search_str(db(), $time_list);

	function db() {
		$db = new \app\sql\mysqlpdo("localhost", 3306, "root", "123456", "test", true);
		return $db;
	}

	function clean($db, $table) {
		$db->query('delete from `'.$table.'`');
	}

	function _count(array &$time_list) {
		return 10000;
		$i = 0;
		foreach ($time_list as $t) {
			$i ++;
		}
		return $i;
	}

	function gen_time() {
		$time_list = array();
		$time = time();
		for ($i = 0; $i < 5000000; $i ++) {
			$time_list[] = $time + $i;
		}
		return $time_list;
	}

	function auto_format(&$time_list, $type) {
		foreach ($time_list as &$t) {
			if ($type == 'str' && is_numeric($t)) {
				$t = date("Y-m-d H:i:s", $t);
			}else if($type == 'int' && !is_numeric($t)){
				$t = strtotime($t);
			}
		}
	}

	function gen_int($db, &$time_list) {
		$num = _count($time_list);
		auto_format($time_list, 'int');

		$begin = mtime() ;
		foreach ($time_list as $time) {
			$me = "me awen";
			$db->query("INSERT INTO `int_time` (`time`, `name`) VALUE ($time,'$me')");
		}
		$cost = mtime() - $begin;

		echo "[total $num] >>> insert `int_time` cost: $cost seconds\n";

	}

	function gen_str($db, &$time_list) {
		$num = _count($time_list);
		auto_format($time_list, 'str');

		$begin = mtime() ;
		foreach ($time_list as $time) {
			$me = "me awen";
			$db->query("INSERT INTO `str_time` (`time`, `name`) VALUE ('$time','$me')");

		}
		$cost = mtime() - $begin;

		echo "[total $num] >>> insert `str_time` cost: $cost seconds\n";

	}

	function mtime() {
		list($second, $msec) = explode(" ", microtime());
		return $second + $msec;
	}

	function search_int($db, array &$time_list) {
		$num = _count($time_list);
		auto_format($time_list, 'int');
		$begin = mtime();

		for ($i = 0; $i < $num; $i ++) {
			$t = $time_list[$i];
			$db->query("SELECT * FROM `int_time` WHERE `time` = $t");
		}

		$cost = mtime() - $begin;

		echo "[total $num] >>> search `int_time` cost: $cost seconds\n";
	}


	function search_str($db, array &$time_list) {
		$num = _count($time_list);
		auto_format($time_list, 'str');

		list($second, $msec) = explode(" ", microtime());
		$time = $second + $msec;

		for ($i = 0; $i < $num; $i ++) {
			$t = $time_list[$i];
			$db->query("SELECT * FROM `str_time` WHERE `time` = '$t'");
		}

		list($second, $msec) = explode(" ", microtime());
		$cost = ($second + $msec) - $time;

		echo "[total $num] >>> search `str_time` cost: $cost seconds\n";
	}

