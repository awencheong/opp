<?php
class A {
	private $data = array(1,2,3,4); 
	public function cal($callable) {
		if (is_callable($callable)) {
			foreach ($this->data as $key => $i) {
				$reduce = $callable($i, $key, $this);
			}
		}
	}
	public function set($key, $val) {
		$this->data[$key] = $val;
	}
	public function data() {
		return $this->data;
	}
}

class Data {
	public static $sum = 0;
}
$a = new A;
$a->cal(function($num) {
	Data::$sum += $num;
});
echo Data::$sum . "\n";

$a->cal(function($num, $i, $data) {
	$data->set($i, $num * $num);
});
foreach ($a->data() as $i => $data) {
	echo "$i \t $data\n";
}
