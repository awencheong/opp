<?php
namespace app;

use app\Mod;

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
        $this->baseNameSpace = str_replace("/", "\\", $this->baseNameSpace);
        $mods = array();
        foreach ($this->cmds as $modPath => $params) {
            $modPath = str_replace("/", "\\", $modPath);
            if (strpos($modPath, "\\") !== 0) {
                $modPath = $this->baseNameSpace . "\\" . trim($modPath, "\\");
            }
            $mods[$modPath] = $params;
        }
        $mod = new Mod();
        return $mod->callSequence($mods, false);
    }
}
