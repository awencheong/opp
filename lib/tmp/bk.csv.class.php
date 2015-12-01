<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/7/3
 * Time: 18:20
 */

class Csv {
	private $sort_columns = array();

	const	RAND_JKEY = '&^Ha-D';
	private $header = array();    //csv 头, 通常对应 数据库表结构,  Html的table元素的 thead

	private $rows;  //数据

	public function format($split = "\t") {
		$lines = array();
		$lines[] = implode($split, $this->header);
		foreach ($this->rows as $r) {
			$lines[] .= implode($split, $r);
		}
		return implode("\n", $lines);
	}

	public function __construct($header) {
		if (is_array($header)) {
			$this->header = array_values($header);
		} else if (is_numeric($header) && $column_num = intval($header)) {
			$this->header = array();
			for ($i = 0; $i < $column_num; $i ++) {
				$this->header[$i] = $i;
			}
		} else {
			throw new Exception("wrong csv header, should be number or array");
		}
		$this->rows = array();
	}

	public function sum(array $fields) {
		$sum = array();
	}

	/* it works like sql "select sum(..) group by (..)"
	 * a new Csv object will be returnd when sum operation is completed
	 *
	 * @return  Csv object
	 */
	public function group_by(array $groupby_fields, array $sum_fields) {
		$csv = new self($this->header);
		if (array_diff($groupby_fields, $sum_fields)) {
			return $csv;
		}
		$groupby_fields = $this->_name2pos($groupby_fields);
		$sums = $this->_name2pos($sum_fields);

		$record = array();
		$row_num = count($this->rows);
		for ($r = 0; $r < $row_num; $r++) {
			$key = array();
			$column_num = count($this->header);
			for ($c = 0; $c < $column_num; $c++) {
				if (isset($groupby_fields[$c])) {
					$key[] = $c;
				}
			}
			$key = md5(implode(Csv::RAND_JKEY, $key));
			if (isset($record[$key])) {
				foreach ($sums as $column => $name) {
					$orig_cell = $csv->cell($record[$key], $column);
					$curr_cell = $csv->cell($r, $column);
					$csv->set_cell($record[$key], $column, $orig_cell + $curr_cell);
				}
			} else {
				$csv->push($this->rows[$r]);
				$record[$key] = $r;
			}
		}
		return $csv;
	}

	public function sub(array $columns) {
		$header = array_values($columns);
		$csv = new self($header);
		$sub_col = array_flip($this->_name2pos($columns));
		foreach ($this->rows as $i => $r) {
			$new = array();
			foreach ($header as $c => $name) {
				$new[$c] = (isset($r[$sub_col[$name]]) ? $r[$sub_col[$name]] : null);
			}
		}
	}

	public function sort_by(array $columns, $desc = true) {
		$sort_columns = $this->_name2pos($columns);
		if (empty($sort_columns)) {
			return false;
		}
		$this->sort_columns = $sort_columns;
		usort($this->rows, array($this, '_sort'));
		return true;
	}

	public function put_rows(array $rows) {
		$rows = array_values($rows);
		if (!isset($rows[0]) || !is_array($rows[0])) {
			return false;
		}
		$column_num = count($this->header);
		$name2pos = $this->_name2pos(array_keys($rows[0]));
		$this->rows = array();
		foreach ($rows as $r) {
			$new = array();
			for ($i = 0; $i < $column_num; $i ++) {
				if (isset($name2pos[$i])) {
					$name = $name2pos[$i];
					if (isset($r[$name]) || isset($r[$i]) &&(($name=$i) || true) ) {
						$new[$i] = $r[$name];
					} else {
						$new[$i] = null;
					}
				} else {
					$new[$i] = null;
				}
			}
			$this->rows[] = $new;
		}
		return true;
	}

	public function push(array $row) {
		if (count($row) != count($this->header)) {
			return false;
		}
		array_push($this->rows, $row);
		return true;
	}

	public function rows() {
		return $this->rows;
	}

	public function row($row_pos) {
		if (isset($this->rows[$row_pos])) {
			return $this->rows[$row_pos];
		} else {
			return array();
		}
	}

	public function column($column_pos) {
		if (is_numeric($column_pos) && intval($column_pos) == $column_pos && isset($this->rows[$column_pos]) && isset($this->rows[0][$column_pos])) {
			$col = array();
			foreach ($this->rows as $r) {
				$col[] = $r[$column_pos];
			}
			return $col;
		} else {
			return array();
		}
	}

	public function cell($row_pos, $column_pos) {
		if (isset($this->rows[$row_pos]) && isset($this->rows[$row_pos][$column_pos])) {
			return $this->rows[$row_pos][$column_pos];
		} else {
			return null;
		}
	}

	public function set_row($row_pos, array $row) {
		if (count($row) == count($this->header) && isset($this->rows[$row_pos])) {
			$this->rows[$row_pos] = $row;
			return true;
		} else {
			return false;
		}
	}

	public function set_column($column_pos, array $column) {
		$row_num = count($this->rows);
		if (count($column) == $row_num && isset($this->header[$column_pos])) {
			$column = array_values($column);
			foreach ($this->rows as $i => &$r) {
				$r[$column_pos] = $column[$i];
			}
			return true;
		} else {
			return false;
		}
	}

	public function set_cell($row_pos, $column_pos, $value, $force = false) {
		if (!$force && (count($this->rows) <= $row_pos  ||  !isset($this->header[$column_pos]))) {
			return false;
		}
		$this->rows[$row_pos][$column_pos] = $value;
		return true;
	}

	public function insert_row($row_pos, array $row) {
		if (count($row) != count($this->header) || ($row_num = count($this->rows)) < $row_pos) {
			return false;
		}
		$row = array_values($row);
		$this->_array_insert($this->rows, $row_pos, $row);
		return true;
	}

	public function insert_column($column_pos, array $column) {
		$row_num = count($this->rows);
		if (count($column) != $row_num || $column_pos >= count($this->header)) {
			return false;
		}
		$column = array_values($column);
		foreach ($this->rows as $i => &$r) {
			$this->_array_insert($r, $column_pos, $column[$i]);
		}
		return true;
	}

	public function size() {
		return array(count($this->rows), count($this->header));
	}

	/* 
	 *  filter those rows which you don't want
 	 *
	 * @param	$filter_func,  callable function,  filter_func($row){ return true|false; }
	 */
	public function filter($filter_func) {
		if (!is_callable($filter_func)) {
			return false;
		}
		$this->rows = array_values(array_filter($this->rows, $filter_func));
		return $this;
	}

	/* 
	 * clone a new Csv object
	 */
	public function copy() {
		return clone $this;
	}

	/*
	 * expand or smallize the csv into a new fit-size Csv object, and return it
	 */
	public function resize($row_num, $column_num = 0) {
		$header = array();
		if ($column_num == 0) {
			$column_num = count($this->header);
		}
		for ($i = 0; $i < $column_num; $i ++) {
			if (isset($this->header[$i])) {
				$header[$i] = $this->header[$i];
			} else {
				$header[$i] = $i;
			}
		}
		$csv = new self($header);
		$csv_rows = $this->rows;
		for ($r = 0; $r < $row_num; $r ++) {
			for ($c = 0; $c < $column_num; $c ++) {
				if (!isset($csv_rows[$r][$c])) {
					$csv_rows[$r][$c] = null;
				}
			}
		}
		$csv->put_rows($csv_rows);
		return $csv;
	}

	/* ===================================================
	 *  all map function will return a new Csv object
	 *  instead of modifing the original csv object
	 * ===================================================
	 * /
	 */
	/*
	 * @param   $map_func,  callable function($val, $row, $column, $csv){}
	 */
	public function map_cell($row, $column, $map_func) {
		if ($column >= count($this->header) || $row >= count($this->rows) || !is_callable($map_func)) {
			return false;
		}
		$this->_map_cell($row, $column, $map_func);
		return true;
	}

	/*
	 * @param   $map_func,  callable function($val, $row, $column, $csv){}
	 */
	public function map_row($row, $map_func) {
		if ($row >= count($this->rows) || !is_callable($map_func)) {
			return false;
		}
		foreach ($this->rows[$row] as $i => $r) {
			$this->_map_cell($row, $i, $map_func);
		}
		return true;
	}

	/*
	 * @param   $map_func,  callable function($val, $row, $column, $csv){}
	 */
	public function map_column($column, $map_func) {
		if ($column >= count($this->header) || !is_callable($map_func)) {
			return false;
		}
		foreach ($this->rows as $i => $r) {
			$this->_map_cell($i, $column, $map_func);
		}
		return true;
	}


	/*
	 * map every cell of $this csv to @param $csv
	 *
	 * @param   $map_func,  callable function($val, $row, $column, $csv1, $csv2)
	 */
	public function map(Csv $csv, $map_func) {
		$new = new self($this->header);
		if (!is_callable($map_func)) {
			return $new;
		}
		$row_num = count($this->rows);
		$column_num = count($this->header);
		for ($r = 0; $r < $row_num; $r ++) {
			for ($c = 0; $c < $column_num; $c ++) {
				$val = call_user_func_array($map_func, array($this->cell($r, $c), $csv->cell($r, $c)));
				$new->set_cell($r, $c, $val, true);
			}
		}
		return $new;
	}


	public function complement(Csv $csv) {
		$complement = new self($this->header);
		$keys = array();
		foreach($csv->rows() as $row) {
			$keys[md5(implode(Csv::RAND_JKEY, $row))] = 1;
		}
		foreach($this->rows as $r) {
			$k = md5(implode(Csv::RAND_JKEY, $r));
			if (isset($keys[$k])) {
				$complement->push($r);
			}
		}
		return $complement;
	}

	public function intersect(Csv $csv) {
		$intersect = new self($this->header);
		$keys = array();
		foreach($csv->rows() as $row) {
			$keys[md5(implode(Csv::RAND_JKEY, $row))] = 1;
		}
		foreach($this->rows as $r) {
			$k = md5(implode(Csv::RAND_JKEY, $r));
			if (isset($keys[$k])) {
				$intersect->push($r);
			}
		}
		return $intersect;
	}

	public function union(Csv $csv) {
		$union = new self($this->header);
		$keys = array();
		foreach($csv->rows() as $row) {
			$k = md5(implode(Csv::RAND_JKEY, $row));
			if (!isset($keys[$k])) {
				$keys[$k] = 1;
				$union->push($row);
			}
		}
		foreach($this->rows as $r) {
			$k = md5(implode(Csv::RAND_JKEY, $r));
			if (!isset($keys[$k])) {
				$keys[$k] = 1;
				$union->push($r);
			}
		}
		return $union;
	}

	private function _name2pos(array $column_names) {
		$pos = array();
		$name2pos = array_flip($this->header);
		foreach ($column_names as $n) {
			if ((is_numeric($n) && intval($n) == $n)  ) {
				if (isset($this->header[$n])) {
					$pos[$n] = $this->header[$n];
				}
			} else {
				if (isset($name2pos[$n])) {
					$pos[$name2pos[$n]] = $n;
				}
			}
		}
		return $pos;
	}

	private function _map_cell($row, $column, $map_func) {
		$this->rows[$row][$column] = call_user_func_array($map_func, array($this->rows[$row][$column], $row, $column, $this));
	}

	private function _sort($row_a, $row_b) {
		foreach ($this->sort_columns as $pos => $name) {
			if ($row_a[$pos] > $row_b[$pos]) {
				return 1;
			} else if ($row_a[$pos] < $row_b[$pos]) {
				return -1;
			}
		}
		return 0;
	}

	private function _array_insert(array &$arr, $pos,$val) {
		if ($pos == 0) {
			array_unshift($arr, $val);
		} else if ($pos == $num) {
			array_push($arr, $val);
		} else {
			$latter_chunk = array_splice($arr, $pos);
			array_unshift($latter_chunk, $val);
			$arr = array_merge($arr, $latter_chunk);
		}
	}
}
