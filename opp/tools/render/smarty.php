<?php

namespace tools\render;
if (!defined('OPP_SMARTY_ROOT')) {
	define('OPP_SMARTY_ROOT', dirname(realpath(__FILE__)) . "/smarty");
}
if (!defined('OPP_SMARTY_INCLUDED')) {
	define('OPP_SMARTY_INCLUDED', 1); 
	include OPP_SMARTY_ROOT . "/libs/Smarty.class.php";
}
class smarty implements \IRender {
	private $path = false;
	private $smarty = null;
	public function __construct($tpl_dir, $path=null) {
		$smarty = new \Smarty();
		$smarty->template_dir = $tpl_dir;
		$smarty->compile_dir = "/tmp";
		$smarty->config_dir = "/tmp";
		$smarty->cache_dir = "/tmp";
		$smarty->left_delimiter = "<{";
		$smarty->right_delimiter = "}>";
		$smarty->caching = false; 
		$this->smarty = $smarty; 
		if ($path) {
			$this->path = $path;
		}
	}

	public function render($path, $params=array()) {
		$this->set_path($path);
		return $this->get_result($params);
	}

	public function set_path($path) {
		$this->path = $path;
	}

	public function get_result($params = array()) {
		if (is_array($params)) {
			foreach ($params as $key => $val) {
				$this->smarty->assign($key, $val);
			}
		} else {
			$this->smarty->assign("_", $params);
		}
		return $this->smarty->fetch($this->path);
	}
}
