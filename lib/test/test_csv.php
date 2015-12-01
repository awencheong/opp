<?php
require "../tester.php";
require "../csv.class.php";

/* 
 *  Csv::resize() 
 *
 */

$c = new Csv(array("name", "age", "score"));
$c->put_rows(
array(
	array("name"=>"awen", "age"=>1, "score"=>6, "aa"),
	array("awen1", 2, 7, "ab"),
	array("awen2", 3, 8, "ac"),
	array("awen3", 4, 9, "ad"),
	array("awen4", 5, 10, "ae"),
)
);

$c->cell(-1,-1)->_should_return(null);
$c->cell(0,0)->_should_return("awen");
$c->cell(4,3)->_should_return("ae");
$c->cell(4,5)->_should_return(null);
$c->cell(5,0)->_should_return(null);

$c->resize(3,4);
$c->cell(3,0)->_should_return(null);
$c->cell(2,3)->_should_return("ac");

$c->resize(3,3);
$c->cell(2,3)->_should_return(null);



/* 
 *  Csv::chose(cols[], rows[]) , 选择特定区域进行操作， 保护其他单元格不被意外改写
 * 
 *  Csv::each_row(callback),  对每一行进行回调
 * 
 *  Csv::each_cell(callback), 对每个单元格进行回调
 * 
 *  Csv::each_col(callback), 对每一列进行回调
 */

$c = new Csv(array("name", "age", "score", "alias"));
$c->put_rows(
array(
	array("name"=>"awen", "age"=>1, "score"=>6, "aa"),
	array("awen1", 2, 7, "ab"),
	array("awen2", 3, 8, "ac"),
	array("awen3", 4, 9, "ad"),
	array("awen4", 5, 10, "ae"),
)
);

$sum = array(1 => 0, 2 => 0);

$c = $c->chose(array(1,2));
$c->each_row(function($row) {
	global $sum;
	$sum[1] += $row[1];
	$sum[2] += $row[2];
});
$c->_result($sum)->_should_equal(array(1 => 15, 2 => 35));


$c = $c->chose(array(1,2));
$c->each_cell(function($value, $row, $col, $csv) {
	$csv->cell($row, $col, $value * $value);
});





/* 
 * Csv:: intersect( Csv $csv) 
 *
 * Csv:: complement( Csv $csv) 
 *
 * Csv:: union( Csv $csv) 
 *
 * Csv:: append( Csv $csv) 
 *
 * Csv:: diff( Csv $csv) 
 */
$a = new Csv(array("name", "age", "score"));
$b = new Csv(array("name", "age", "score"));

$a->put_rows(array(
	array("awen1", 2, 7),
	array("awen2", 3, 8),
	array("awen3", 4, 9),
));

$b->put_rows(array(
	array("awen1", 2, 7),
	array("awen2", 3, 8),
	array("awen3", 4, 9),
));


/* 
 *  Csv::each_row(Csv $csv, callback),  对每一行进行回调
 * 
 *  Csv::each_cell(Csv $csv, callback), 对每个单元格进行回调
 * 
 *  Csv::each_col(Csv $csv, callback), 对每一列进行回调
 *
 *  Csv::search(cols[], value[]), 搜索行
 *
 *
 */
$a = new Csv(array("name", "age", "score"));
$b = new Csv(array("name", "age", "score"));
$c = new Csv(array("name", "age", "score"));

$a->put_rows(array(
	array("awen1", 2, 7),
	array("awen2", 3, 8),
	array("awen3", 4, 9),
));

$b->put_rows(array(
	array("awen1", 2, 7),
	array("awen2", 3, 8),
	array("awen3", 4, 9),
));

$a->chose(array(1,2))->each_cell($b, function($va, $vb, $row, $col, $a, $b) {
	$a->cell($row, $col, $va + $vb);
	$b->cell($row, $col, $va * $vb);
});

$b = $b->group_by(array(1,2));
$b->each_row(function($va, $vb, $b){
});

$a->group_by(array(1,2))->each_row(
