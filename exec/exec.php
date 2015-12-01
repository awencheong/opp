<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/9/1
 * Time: 10:58
 */

/* ===============================================
 *  require   >PHP5  see http://php.net/manual/en/class.reflectionmethod.php
 * ===============================================
 */
namespace Mob\console;


include __DIR__ . "/include.php";

use \Mob\console\lib\Exec;

global $argv;
$cmd = new Exec($argv);
$cmd->run("\\Mob\\console\\modules");



