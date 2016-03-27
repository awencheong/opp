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
use app\Mod;
global $argv;
try {
    $cmd = new Cmd($argv);
    if (!$cmd->cmds) {
        die("usage: php " . $cmd->script . " --cmd1 param1 ...  --cmd2 param1 ... \n\n");
    }
    Mod::$SAFE_MODE = false;
    Mod::$baseNameSpace = "myapp/modules";
    $fp = fopen("php://stdin", "r");
    if (!$fp) {
	    die("failed to open stdin\n");
    }
    Mod::initSequence($cmd->cmds);
    while (!feof($fp)) {
	    $line = trim(fgets($fp), "\n"); 
	    if ($line) {
		    print_r(Mod::callSequence($line));
		    echo "\n";
	    }
    }
    fclose($fp);
    
} catch (\Exception $e) {
    echo ($e->getMessage() . "\n");
}
