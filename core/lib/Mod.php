<?php
namespace app\lib;

class Mod
{

    private $started = false;

    public $res;

    /*
     * @param $mods, array( 'modPath' => $params )
     */
    public function callSequence(array $mods, $map_param_names = true)
    {
        foreach ($mods as $path => $params) {
            $this->call($path, $params, $map_param_names);
        }
        $this->started = false;
        $res = $this->res;
        $this->res = null;
        return $res;
    }

    public function call($modPath, $params, $map_param_names = true)
    {
        if (!$params) {
            $params = array();
        }
        foreach ($params as &$p) {
            if ($p == '$1' && $this->started) {
                $p = $this->res;
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
        if (!$params && $this->started) {
            $params = array(
                $this->res
            );
            $map_param_names = false;
        }
        $callable = $this->_findFunc($modPath);
        if ($map_param_names) {
            $tmp = $params;
            $params = array();
            if ($callable['type'] == 'func') {
                //1. function
                $ref = new \ReflectionFunction($callable['name']);
            } else {
                //2. class method
                $ref = new \ReflectionMethod($callable['name']);
            }
            foreach ($ref->getParameters() as $pm) {
                $pname = $pm->getName();
                if (isset($tmp[$pname])) {
                    $params[] = $tmp[$pname];
                } else {
                    $params[] = null;
                }
            }
        }
        $res = call_user_func_array($callable['callable'], $params);
        $this->started = true;
        $this->res = $res;
        return $this->res;
    }

    private function _findFunc($modPath)
    {
        //1. find function
        //2. find class::method
        //3. throw exception
        if (function_exists($modPath)) {
            return array(
                'callable' => $modPath,
                'name' => $modPath,
                'type' => 'func'
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
            'callable' => array(
                new $class(),
                $method
            ),
            'name' => $class . "::" . $method,
            'type' => 'method'
        );
    }
}
