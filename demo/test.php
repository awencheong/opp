<?php

include "../opp/opp.php";

opp()->db = new \opp\sql\mysqlpdo("127.0.0.1", 3306, "root", "root", "handone");
opp()->db->query('insert into `test` (`value`) values (\'%\');');

/* test sql 

/*
 * test caller
 *
 *
 *
 
opp()->request = new \opp\request\web;
$c = new \opp\caller\obj("example", __DIR__);
$r = new \opp\render\json();
echo $r->get_result($c->call("dot/sth"));
 */


/* 
 *
 * test request
 *
 * 

$request = new \opp\request\web;
$render = new \opp\render\json();
$params = $request->get_params();
$params['path'] = $request->get_path();
echo $render->get_result($params);

 *
 */



/*
 *
 * test render
 * 

$render = new \opp\render\smarty(OPP_ROOT . "/themes");
$render = new \opp\render\json(OPP_ROOT . "/themes");
$result = array(
	"title" => "top", 
	"sublist" => array( 
		array(
			"title" => "第一级",
			"sublist" => array(
				array (
					"title" => "第二级", 
					"sublist" => array(
						array (
							"title" => "第三季",
							"sublist" => array(),
						),
					),
				)
			)
		),
	)
);
echo $render->render("grey/menu_lev_3.html", array("menu" => $result));


 */
