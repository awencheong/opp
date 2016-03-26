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

use opp\core\lib\Cmd;
global $argv;
try {
    $cmd = new Cmd($argv);
    if (!$cmd->cmds) {
        die("usage: php " . $cmd->script . " --cmd1 param1 ...  --cmd2 param1 ... \n\n");
    }
    print_r($cmd->exec("myapp/modules"));
} catch (\Exception $e) {
    echo ($e->getMessage() . "\n");
}
echo "\n";
