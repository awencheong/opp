<?php

include "Pipe.php";

class Mapper1 {
	public function map($key, $val) {
		list($name, $class, $age) = explode(":", $val);
		return array(
			"key" => $name,
			"val" => array("class" => $class, "age" => $age)
		);
	}
}

class Mapper2 {
	public function map($key, $val) {
		return array(
			array("key" => $key, "val" => $val),
			array("key" => $key, "val" => $val),
			array("key" => $key, "val" => $val)
		);
	}
}

class Reducer1 {
	public function reduce($key, array $values) {
		$classSum = $ageSum = 0;
		foreach ($values as $v) {
			$classSum += $v['class'];
			$ageSum += $v['age'];
		}
		return array(
			"key" => $key,
			"val" => array(
				"class" => $classSum,
				"age" => $ageSum
			)
		);
	}
}

class Mapper3 {
	public function map($key, $val) {
		if ($key == "a") {
			return array(
				array(
					"key" => "a",
					"val" => $val['class']
				),
				array(
					"key" => "a",
					"val" => $val['age']
				)
			);
		}
	}
}

class Reducer2 {
	public function reduce($key, array $values) {
		return array(
			'key'=>$key,
			'val'=>array_sum($values)
		);
	}
}

$in = new ArrKeyVal(
	array(
		"a:1:123",
		"b:1:123",
		"b:1:123",
	)
);
$p = new Pipe;
$p->addInput($in);
$p->setFilterList(array(
	new Mapper1,
	new Mapper2,
	new Reducer1,
	new Mapper3,
	new Reducer2
));
$out = $p->fetch();
print_r($out);

$in = new ArrKeyVal(
	array(
		"a:1:123",
		"b:1:123",
		"b:1:123",
	)
);
$p = new Pipe;
$p->addInput($in);
$p->setFilterList(array(
	new Mapper1,
	new Mapper2,
	new Reducer1,
));
$out = $p->fetch();
print_r($out);
