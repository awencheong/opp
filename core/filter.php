<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/9/1
 * Time: 10:58
 *
 */

/*
 * ===============================================
 * require >PHP5 see http://php.net/manual/en/class.reflectionmethod.php
 * ===============================================
 */
namespace opp\core;

include __DIR__ . "/include.php";

// command:  echo "abc" | php filter.php --Index/run "awen" '$1' --/json_encode '$1' 

use app\Cmd;
$cmd = new Cmd($argv);
$cmd->filter("myapp/modules");
