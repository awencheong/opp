<?php
namespace	app;

class Cmd
{

    private $baseNameSpace = '';

    private $argv;

    private $return = array(
        'started' => false,
        'data' => null
    );

    public $cmds = array();

    public $script;

    public function __construct($argv = null)
    {
        $this->argv = $argv;
        if ($this->argv === null) {
            global $argv;
            $this->argv = $argv;
        }
        $this->_init();
    }

    private function _init()
    {
        $this->script = $this->argv[0];
        $argv = array_slice($this->argv, 1);
        $cmd = '';
        foreach ($argv as $a) {
            if (preg_match('/^--([^-^\s]+)/', $a, $match)) {
                $cmd = $match[1];
                continue;
            }
            
            if ($cmd != '') {
                if ($tmp = \json_decode($a, true)) {
                    $this->cmds[$cmd][] = $tmp;
                } else {
                    $this->cmds[$cmd][] = $a;
                }
            }
        }
    }

    public function init(array $argv = null)
    {
        $this->argv = $argv;
        $this->_init();
    }

    public function exec($namespace = "/")
    {
        $this->baseNameSpace = "/" . trim($namespace, "/");
        if ($this->baseNameSpace == "/") {
            $this->baseNameSpace = "";
        }
        foreach ($this->cmds as $modPath => $params) {
            $this->_exec($modPath, $params);
        }
        return $this->return['data'];
    }

    private function _exec($modPath, $params)
    {
        foreach ($params as &$p) {
            if ($p == '$1' && $this->return['started']) {
                $p = $this->return['data'];
                continue;
            }
            
            if (is_string($p) && preg_match('/^@(.*)/', $p, $match)) {
                $filename = $match[1];
                if (file_exists($filename) && is_readable($filename)) {
                    $str = file_get_contents($filename);
                    if (preg_match('/\.json$/', $filename)) {
                        $p = json_decode($str, true);
                    } else {
                        $p = $str;
                    }
                } else {
                    $p = '';
                }
            }
        }
        $res = call_user_func_array($this->_findFunc($this->baseNameSpace . "/" . trim($modPath, "/")), $params);
        $this->return['started'] = true;
        $this->return['data'] = $res;
        return;
    }

    private function _findFunc($modPath)
    {
        //1. find function
        //2. find class::method
        //3. throw exception
        $modPath = str_replace("/", "\\", $modPath);
        if (function_exists($modPath)) {
            return $modPath;
        }
        $split = strrpos($modPath, "\\");
        $class = substr($modPath, 0, $split);
        $method = substr($modPath, $split + 1);
        if (!$method) {
            throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
        }
        if (!class_exists($class)) {
            throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
        }
        $obj = new $class();
        if (!method_exists($obj, $method)) {
            throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
        }
        return array(
            $obj,
            $method
        );
    }
}