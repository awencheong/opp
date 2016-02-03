<?php
namespace Mob\console\lib;

class Doc
{

    public function helpClass($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = array(
                $keywords
            );
        }
        $doc = array();
        foreach (get_declared_classes() as $class) {
            foreach ($keywords as $k) {
                if ($this->match($class, $k)) {
                    $ref = new \ReflectionClass($class);
                    $methods = $ref->getMethods();
                    foreach ($methods as $m) {
                        $mname = $m->getName();
                        $params = array();
                        foreach ($m->getParameters() as $p) {
                            $params[] = $this->params($p);
                        }
                        $doc[$class][$mname] = $params;
                    }
                }
            }
        }
        return $doc;
    }

    public function helpFunc($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = array(
                $keywords
            );
        }
        $funs = get_defined_functions();
        $doc = array();
        foreach ($funs['user'] as $f) {
            foreach ($keywords as $k) {
                if ($this->match($f, $k, true)) {
                    $ref = new \ReflectionFunction($f);
                    foreach ($ref->getParameters() as $p) {
                        $doc[$f][] = $this->params($p);
                    }
                }
            }
        }
        return $doc;
    }

    private function params(\ReflectionParameter $p)
    {
        $desc = array(
            'name' => $p->getName(),
            'isArray' => false
        );
        if ($p->isDefaultValueAvailable()) {
            $desc['default'] = $p->getDefaultValue();
        }
        if ($p->isArray()) {
            $desc['isArray'] = true;
        }
        return $desc;
    }

    private function match($funcName, $keyword, $caseInsensitive = false)
    {
        if ($caseInsensitive) {
            $funcName = strtolower($funcName);
            $keyword = strtolower($keyword);
        }
        return strpos($funcName, $keyword) !== false;
    }
}
