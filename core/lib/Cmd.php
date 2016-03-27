<?php
namespace app;

class Cmd
{

	private $baseNameSpace = '';

	private $argv;

	private $return = array(
		'started' => false,
		'data' => null
	);

	public $boost_conf = array();

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

	public function find($cmdName) 
	{
		foreach ($this->cmds as $c) {
			if ($c['mod'] == $cmdName) {

			}
		}
	}

	private function _init()
	{
		$this->script = $this->argv[0];
		$argv = array_slice($this->argv, 1);
		$i = -1;
		$cmd = null;
		foreach ($argv as $a) {
			if (preg_match('/^--(.+)$/', $a, $match)) {
				$cmd = $match[1];
				$options = array();
				if (preg_match('/^(.+)\[([^\[\]]+)\]$/', $cmd, $match)) {
					$cmd = $match[1];
					$op_cmds = array_filter(explode(",",trim($match[2])));
					foreach ($op_cmds as $op) {
						$val = true;
						if (($pos = strpos($op, ":")) !== false) {
							$val = substr($op, $pos+1);
						}
						$options[$op] = $val;
					}
				} else {
					$options = array();
				}
				$this->cmds[++$i] = array(
					"mod" => $cmd,
					"options" => $options,
					"params" => array()
				);
				continue;

			} else {
				if ($cmd != null) {
					$this->_putParam($i, $a);
				} else {
					$this->boost_conf[] = $this->_autoJsonDecode($a);
				}
			}

		}
	}

	private function _putParam($i, $param) {
		$forbid_std_in = isset($this->cmds[$i]['options']['param_forbid_std']) && $this->cmds[$i]['options']['param_forbid_std'];
		if ($param == '$1' && !$forbid_std_in) {
			$this->cmds[$i]['options']['param_from_std'] = count($this->cmds[$i]['params']);
		}
		$this->cmds[$i]['params'][] = $this->_autoJsonDecode($param);
	}

	private function _autoJsonDecode($param) {
		if ($json = json_decode($param, true)) {
			return $json;
		} else {
			return $param;
		}
	}

	public function init(array $argv = null)
	{
		$this->argv = $argv;
		$this->cmds = array();
		$this->_init();
	}

}
