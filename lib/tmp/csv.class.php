<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/7/3
 * Time: 18:20
 */

class Csv {
	const	RAND_JKEY = '&^Ha-D';

	private $data = array();
	private $rows_num = 0;
	private $cols_num = 0;

	private $mask_range = array();	// ['from'=>[row, col], 'to' => [row, col]]
	private $mask_cells = array();	// [row => [col0, col1] , row2 => [col0, col1] .... ]

	private $cols_chosed = array();
	private $rows_chosed = array();



	/* lock some cells in the specified range of csv, to get ready for 'mapping' methods like map()/reduce() 
	 *
	 * @param	$range, ['from' => (row, col), 'to' => (row, col),  'cells' =>[ (row, col), (row, col) .... ]]
	 *			1. if 'cells' is provided, those cells will be chosen
	 *			2. other wise, the range 'from' to 'to' will be chosen
	 *
	 * @return	this
	 */
	public function mask(array $range) {
	}

	private function _range_ok($from_row, $from_col, $to_row, $to_col) {
		return $from_row == intval($from_row) && $from_col == intval($from_row) && $to_row == intval($to_row) && $to_col = intval($to_col);
	}

	public function mask_rows(array $rows) {
		
	}

	public function mask_cols(array $cols) {
	}

	public function unmask() {
	}

	
	/* map the cells which are masked in csv, see Csv::mask() 
	 *
	 * @param	$callback,  a method/func 
	 * @param	Csv $csv,	optional, another csv 
	 *		if $csv == null,   param $callback form should be :  func( val, [row, col, this])
	 *		elseif  $csv != null,  param $callback form should be :  func( val1, val2, [row, col, this, csv]), in which  val1 comes from this, and val2 comes from $csv
	 *
	 * @return	a new csv object
	 */
	public function map($callback, Csv $csv = null) {
	}


	/* reduce the cells which are masked in csv, see Csv::mask()
	 *
	 * @param	$callback,   in form :  func( val, reduce, [row, col, this] )
	 *
	 * @return	a new csv object
	 */
	public function reduce($callback) {
	}


	/* filter the rows which are chosed
	 *
	 * @param	$callback,  in form : func(row, this) { return true / false; }
	 * 
	 * @return	a new Csv object
	 */
	public function filter($callback) {
	}


	
	/* complement two csvs
	 *
	 * @return	a new csv object
	 */
	public function complement(Csv $csv, $callback) {
	}

	/* intersect two csvs
	 *
	 * @return	a new csv object
	 */
	public function intersect(Csv $csv, $callback) {
	}

	/* union two csvs
	 *
	 * @return	a new csv object
	 */
	public function union(Csv $csv, $callback) {
	}
	
	/* append `this` csv into another csv
	 *
	 * @return	a new csv object
	 */
	public function append_to(Csv $csv) {
	}


	/* get/set cell
	 *
	 * @param	$value,  if $value !== null, the $value will be set into the cell of csv
	 *
	 * @return	value, or null 
	 */
	public function cell($row, $col, $value=null) {
	}

	/* get/set row
	 *
	 * @param	$value,  if $value !== null, the $value will be set into the row of csv
	 *
	 * @return	array
	 */
	public function row($row, $value=null) {
	}

	/* get/set column
	 *
	 * @param	$value,  if $value !== null, the $value will be set into the column of csv
	 *
	 * @return	array
	 */
	public function col($column, $value=null) {
		if (!$this->_col_ok($column)) {
			return array();
		}
		if (is_array($value)) {
			foreach ($value as $r => $val) {
				if (!is_numeric($r) || intval($r) != $r || $this->_row_ok($r)) {
					continue;
				}
				if (!is_numeric($val) && !is_string($val)) {
					continue;
				}
				$this->data[$r][$column] = $val;
			}
		}
		$cols = array();
		foreach ($i = 0; $i < $this->rows_num; $i ++) {
			$this->data[$r][$column] = $name;
		}
	}

	/* get size of csv
	 *
	 * @return	[row_num, col_num]
	 */
	public function size() {
		return array($this->rows_num, $this->cols_num);
	}

	/* reset the size of csv
	 *
	 * @param	$size, array of size: [row_num, col_num]
	 */
	public function resize(array $size) {
		if (!isset($size[0]) || !isset($size[1]) || intval($size[0]) != $size[0] || intval($size[1]) != $size[1] || $size[0] <= 0 || $size[1] <= 0) {
			return false;
		}
		$this->rows_num = $size[0];
		$this->cols_num = $size[1];
		return true;
	}

	
	/* return array of the cells, in which if there are no data, nulls will be given
	 *
	 * @return	array()
	 */
	public function rows() {
		$rows = empty($this->rows_chosed) ? (array_keys(array_fill(0, $this->rows_num, 0))) : $this->rows_chosed;
		$cols = empty($this->cols_chosed) ? (array_keys(array_fill(0, $this->cols_num, 0))) : $this->cols_chosed;
		$list = array();
		foreach ($rows as $r) {
			foreach ($cols as $c) {
				$list[$r][$c] = $this->_cell($r, $c);
			}
		}
		$this->_unchose();
		return $list;
	}

	private function _cell($row, $col, $val = null) {
		if ($this->rows_num > $row && $this->cols_num > $col && isset($this->data[$row][$col])) {
			return $this->data[$row][$col];
		} else {
			return null;
		}
	}

	private function _col_ok($col) {
		return $this->cols_num > $col;
	}

	private function _row_ok($row) {
		return $this->rows_num > $row;
	}

	private function _unchose() {
		$this->cols_chosed = array();
		$this->rows_chosed = array();
	}
}
