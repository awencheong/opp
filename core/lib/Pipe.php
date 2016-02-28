<?php
abstract class KeyValInput {
	public function open(){}
	public abstract function hasNext();
	public abstract function next(&$key, &$val);
	public function close(){}
}

class ArrKeyVal extends KeyValInput {
	private $data;
	public function __construct(array $data) {
		$this->data = $data;
	}
	public function hasNext() {
		return !empty($this->data);
	}
	public function next(&$key, &$val) {
		list($key, $val) = each($this->data);
		unset($this->data[$key]);
	}
}

class	Pipe {
	private $caches = array();	//[name => vale]
	private function setCache($name, $value) { 
		$this->caches[$name] = $value; 
	}
	
	private $inputs = array();	//list of instance of KeyValInput
	public function addInput( KeyValInput $input ){ 
		$this->inputs[] = $input;
	}

	private $filters = array();
	public function setFilterList(array $list) {
		$unit = array("m"=>array(), "r"=>null, "empty" => true);
		foreach ($list as $f) {
			if ($this->isMapper($f)) {
				$unit["m"][] = $f;
				$unit["empty"] = false;
				continue;
			} elseif ($this->isReducer($f)) {
				$unit["r"] = $f;
				$this->filters[] = $unit;
				$unit = array("m"=>array(), "r"=>null, "empty"=>true);
				continue;
			} else {
				throw new Exception("wrong fillter list while calling Pipe::setFilterList(array \$list)");
			}
		}
		if ($unit["empty"] == false) {
			$this->filters[] = $unit;
		}
	}

	private function isMapper($f) {
		return method_exists($f, "map");
	}

	private function isReducer($f) {
		return method_exists($f, "reduce");
	}
	

	public function fetch() {
		$filters = $this->filters;
		if (empty($filters)) {
			return ;
		}
		$fir = array_shift($filters);
		$output = $this->filter($this->inputs, $fir["m"], $fir["r"]);
		foreach ($filters as $f) {
			$output  = $this->filter(array(new ArrKeyVal($output)), $f["m"], $f["r"]);
		}
		return $output;
	}

	private function map(array $pairs, $map) {
		$new_pairs = array();
		foreach ($pairs as $p) {
			$line = $map->map($p[0], $p[1]);
			if (!$line) {
				continue;
			}
			$new_pairs = array_merge($new_pairs, $this->newPairs($line, $m, "m"));
		}
		return $new_pairs;
	}

	private function filter(array $inputs, array $mappers, $reducer) {
		$output = array();
		foreach ($inputs as $in) {
			$in->open();
			while ($in->hasNext()) {
				$in->next($key, $val);
				$pairs = array(array($key, $val));
				foreach ($mappers as $m) {
					$pairs = $this->map($pairs, $m);
				}
				foreach ($pairs as $p) {
					$output[$p[0]][] = $p[1];
				}
			}
			$in->close();
		}
		$new_output = array();
		if ($reducer) {
			foreach ($output as $key => $val) {
				$line = $reducer->reduce($key, $val);
				if (!$line) {
					continue;
				}
				$pairs = $this->newPairs($line, $reducer, "r");
				unset($output[$key]);
				foreach ($pairs as $p) {
					list($k, $v) = $p;
					$new_output[$k] = $v;
				}
			}
			$output = $new_output;
		}
		return $output;
	}

	private function haltAtWrongLine($m, $line, $filterType = 'm') {
		if ($filterType == 'm') {
			$log_method = get_class($m)."::map(\$key,\$val),";
		} else {
			$log_method = get_class($m)."::reduce(\$key,\$val),";
		}
		throw new Exception("wrong data returned from method ".$log_method." ,".json_encode($line));
	}

	private function newPairs($line, $filter, $filterType = "m") {
		if (!is_array($line)) {
			$this->haltAtWrongLine($filter, $line, $filterType);
		}
		if ($this->isSinglePair($line, $k, $v)) {
			return array(array($k, $v));
		} else {
			$pairs = array();
			foreach ($line as $l) {
				if (!is_array($l)) {
					$this->haltAtWrongLine($filter, $line, $filterType);
				}
				if (!$this->isSinglePair($l, $k, $v)) {
					$this->haltAtWrongLine($filter, $line, $filterType);
				}
				$pairs[] = array($k, $v);
			}
			return $pairs;
		}
	}

	private function isSinglePair($line, &$key, &$val) {
		$ok = key_exists('key', $line) && (is_string($line['key']) || is_numeric($line['key']) || $line['key'] == null) 
			&& key_exists('val', $line);
		if ($ok) {
			$key = $line['key'];
			$val = $line['val'];
		}
		return $ok;
	}

}
