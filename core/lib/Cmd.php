<?php
namespace app;
use	app\Mod;

class Cmd
{

	private $argv;

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


	public function exec($namespace = "/")
	{
		if (!$this->cmds) {
			die("usage: php " . $cmd->script . " --cmd1 param1 ...  --cmd2 param1 ... \n\n");
		}
		$cmds = $this->cmds;
		try {
			$op = $cmds[0]['options'];
			$data = null;
			if (isset($op['param_from_std']) && $op['param_from_std'] >= 0) {
				$data = file_get_contents("php://stdin");
			}

			Mod::$SAFE_MODE = false;
			Mod::$baseNameSpace = $namespace;

			Mod::initSequence($cmds);
			print_r(Mod::callSequence($data));
			echo "\n";
		} catch (\Exception $e) {
			file_put_contents("php://stderr", $e->getMessage() . "\n");
		}
	}

	public function filter($namespace = "/")
	{
		if (!$this->cmds) {
			die("usage: php " . $cmd->script . " --cmd1 param1 ...  --cmd2 param1 ... \n\n");
		}
		$fp = null;
		try {
			Mod::$SAFE_MODE = false;
			Mod::$baseNameSpace = $namespace;
			$fp = fopen("php://stdin", "r");
			if (!$fp) {
				die("failed to open stdin\n");
			}
			Mod::initSequence($this->cmds);
			while (!feof($fp)) {
				$line = trim(fgets($fp), "\n");
				if ($line) {
					print_r(Mod::callSequence($line));
					echo "\n";
				}
			}
			fclose($fp);
		} catch (\Exception $e) {
			if ($fp) {
				fclose($fp);
			}
			file_put_contents("php://stderr", $e->getMessage() . "\n");
		}
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
