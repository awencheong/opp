<?php
/**
 * Created by PhpStorm.
 * User: awencheong
 * Date: 2015/10/30
 * Time: 14:25
 */
namespace Mob\console\lib;

class Exec
{
    private $params = array();
    public function __construct($params)
    {
        $this->params = $params;
    }

    public function run($namespace)
    {
        $params = $this->params;
        $script_name = $params[0];
        $cmd = isset($params[1]) ? $params[1] : '_';
        $cmd = str_replace("/", "\\", $cmd);
        $class = $namespace . "\\" . $cmd;
        if (!class_exists($class)) {
            if ($cmd != '_') {
                $err = "class $class not found\n\n";
            } else {
                $err = '';
            }
            die("usage: $script_name cmd [options][--help] \n\n\t $err\n");
        }

        /* get cmd usage */
        $usage = array();
        foreach (get_class_methods($class) as $method) {
            $op = "--" . $method;
            if ($method != '__construct') {
                $method = new \ReflectionMethod($class, $method);
                $usage[$op] = array();
                foreach ($method->getParameters() as $p) {
                    if (preg_match('/^[a-z].*$/', substr($p->name, 0, 1))) {
                        $usage[$op][] = $p->name;
                    }
                }
            }
        }
        $params[0] .= " " . $params[1];
        $cmd_argv = Cmd::init($usage, $params);


        $cmd = new $class;
        foreach ($cmd_argv as $op => $val) {
            if (preg_match('/^--[a-zA-Z].*$/', $op)) {
                $op = trim($op, '-');
                if (!is_array($val)) {
                    try {
                        $res = call_user_func_array(array($cmd, $op), array());
                    } catch (\Exception $e) {
                        $res = $e;
                    }

                } else {
                    try {
                        $res = call_user_func_array(array($cmd, $op), $val);
                    } catch (\Exception $e) {
                        $res = $e;
                    }
                }
                if ($res === null) {
                    echo "succ\n";
                } else if (is_array($res)){
                    print_r($res);
                    echo "\n";
                } else if (is_numeric($res)) {
                    echo $res . "\n";
                } else if (is_string($res) && $res) {
                    echo $res . "\n";
                } else if ($res instanceof \Exception) {
                    print_r($res->getMessage() . "\n");
                }
            }
        }
    }
}

