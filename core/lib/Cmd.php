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

    private $op = 'EXEC';

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

    public function lastOperation()
    {
        return $this->op;
    }

    private function _init()
    {
        $this->op = 'EXEC';
        $this->script = $this->argv[0];
        $argv = array_slice($this->argv, 1);
        $cmd = '';
        foreach ($argv as $a) {
            if (preg_match('/^--(.+)$/', $a, $match)) {
                $cmd = $match[1];
                $this->cmds[$cmd] = array();
                continue;
            }
            
            if ($cmd != '') {
                if ($tmp = json_decode($a, true)) {
                    $this->cmds[$cmd][] = $tmp;
                } else {
                    $this->cmds[$cmd][] = $a;
                }
            }
        }
        
        if (isset($this->cmds['HELP'])) {
            $this->op = 'HELP';
            unset($this->cmds['HELP']);
        }
    }

    public function init(array $argv = null)
    {
        $this->argv = $argv;
        $this->cmds = array();
        $this->_init();
    }

    public function exec($namespace = "/")
    {
        $this->baseNameSpace = "/" . trim($namespace, "/");
        if ($this->baseNameSpace == "/") {
            $this->baseNameSpace = "";
        }
        foreach ($this->cmds as $modPath => $params) {
            if (strpos($modPath, "/") !== 0) {
                $modPath = $this->baseNameSpace . "/" . trim($modPath, "/");
            }
            switch ($this->op) {
                case 'HELP':
                    $this->_helpFunc($modPath);
                    break;
                case 'EXEC':
                    $this->_exec($modPath, $params);
                    break;
            }
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
        if (!$params && $this->return['started']) {
            $params = array(
                $this->return['data']
            );
        }
        $callable = $this->_findFunc($modPath);
        //$res = call_user_func_array($callable[0], $params);
        if ($callable[0] instanceof \ReflectionMethod) {
            $res = $callable[0]->invokeArgs($callable[1], $params);
        } else {
            $res = $callable[0]->invokeArgs($params);
        }
        $this->return['started'] = true;
        $this->return['data'] = $res;
        return;
    }

    private function _helpFunc($modPath)
    {
        $res = array(); // name[=123]
        try {
            $refFunc = $this->_findFunc($modPath);
            foreach ($refFunc[0]->getParameters() as $p) {
                $desc = array();
                if ($p->isDefaultValueAvailable()) {
                    $desc['default'] = $p->getDefaultValue();
                }
                if ($p->isArray()) {
                    $desc['isArray'] = true;
                }
                $res[$p->getName()] = $desc;
            }
        } catch (\Exception $e) {
            $res = $e->getMessage();
        }
        $this->return['data'][$modPath] = $res;
    }

    private function _findFunc($modPath)
    {
        //1. find function
        //2. find class::method
        //3. throw exception
        $modPath = str_replace("/", "\\", $modPath);
        if (function_exists($modPath)) {
            return array(
                new \ReflectionFunction($modPath)
            );
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
        if (!method_exists($class, $method)) {
            throw new \Exception("failed to find module;  function [$modPath]  or  method $class::$method not found");
        }
        return array(
            new \ReflectionMethod($class, $method),
            new $class()
        );
    }
}
