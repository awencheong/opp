<?php
namespace Mob\console\lib;

class Path2Method
{

    /*
     * 根据 $path， 在命名空间$namespace下 找到对应的 class 和 method, 返回该method 的实例 array($obj, $method),
     * 可使用在 call_user_func(), call_user_func_array() 等函数上;
     *
     * @param $namespace, 根命名空间
     * @param $path, 至少两个组成， 例如 classA/methodB
     *
     * @return 成功返回 method 实例， 失败返回 false
     */
    public function handler($namespace, $path)
    {
        $path = array_filter(explode("/", $path));
        if (count($path < 2)) {
            throw new \Exception("wrong path, at least 2 pieces");
        }
        
        $method = array_pop($path);
        $class = $namespace . "\\" . implode("\\", $path);
        if (!class_exists($class)) {
            throw new \Exception("class not found $class");
        }
        
        if (method_exists($class, $method)) {
            $obj = new $class();
            return array(
                $obj,
                $method
            );
            ;
        } else {
            throw new \Exception("method $class::$method() not exists");
        }
    }
}