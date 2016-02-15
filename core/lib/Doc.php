<?php
namespace app;

class Doc
{

    private $include_files = array();

    private function includeFiles($dirname)
    {
        if (isset($this->include_files[$dirname])) {
            return;
        }
        $this->include_files[$dirname] = true;
        $this->_includeFiles($dirname);
    }

    private function _includeFiles($dirname)
    {
        if (!is_dir($dirname)) {
            ob_start();
            $old = error_reporting();
            error_reporting(E_STRICT);
            include_once $dirname;
            error_reporting($old);
            ob_end_clean();
            return;
        }
        foreach (scandir($dirname) as $file) {
            if ($file != '..' && $file != '.') {
                $this->_includeFiles($dirname . "/" . $file);
            }
        }
    }

    public function helpClass($dirname, $keywords)
    {
        $this->includeFiles($dirname);
        $keywords = $this->fmtKeywods($keywords);
        $doc = array();
        foreach (get_declared_classes() as $class) {
            foreach ($keywords as $k) {
                $ref = new \ReflectionClass($class);
                $methods = $ref->getMethods();
                foreach ($methods as $m) {
                    $mname = $m->getName();
                    $params = array();
                    foreach ($m->getParameters() as $p) {
                        $params[] = $this->params($p);
                    }
                    if ($this->match($class . "\\" . $mname, $k)) {
                        $doc[$class][$mname] = $params;
                    }
                }
            }
        }
        return $doc;
    }

    private function fmtKeywods($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = array(
                $keywords
            );
        }
        foreach ($keywords as $i => $k) {
            $keywords[$i] = str_replace("/", "\\", $k);
        }
        return $keywords;
    }

    public function helpFunc($dirname, $keywords)
    {
        $this->includeFiles($dirname);
        $keywords = $this->fmtKeywods($keywords);
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
