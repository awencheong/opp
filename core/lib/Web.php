<?php

namespace	app;
use	app\Mod;

class	Web
{
	public $path;
	public $method;
	public $get = array();
	public $post = array();

	/*
	 *
	 array (
		 '/admin/*' =>[ 
		 	array(	'mod' => $m_path,
				 'params' => array(),
				 'options' => array('param_map'=>true),
				 'mod_preg' => array(['upper' => false, 'replace' => 'xx'])
				 'param_preg' => array([ 0 => ''])
			 ),
			 ...
		]
	 )
	 *
	 */
	private $route = array(
		'auth' => array(
			'eq' => array(),
			'prefix' => array(),
			'sufix' => array()
		),
		'mod' => array(
			'eq' => array(),
			'prefix' => array(),
			'sufix' => array()
		),
		'view' => array(
			'eq' => array(),
			'prefix' => array(),
			'sufix' => array()
		)
	);

	const 	ROUTE_LEV_EQU = 'eq';
	const	ROUTE_LEV_PREFIX = 'prefix';
	const	ROUTE_LEV_SUFIX = 'sufix';


	public function __construct()
	{
		$this->method = @$_SERVER['REQUEST_METHOD'];
		if ($this->method == 'GET') {
 			$this->get = $_GET;
		} else if ($this->method == 'POST') {
			$this->post = $_POST;
		}
		$path = parse_url(@$_SERVER['DOCUMENT_URI'], PHP_URL_PATH);
		$this->path = $path;
	}

	private function getInput()
	{
		if ($this->method == "GET") {
			$input = $this->get;
		} elseif ($this->method == "POST") {
			$input = $this->post;
		} else {
			$input = array();
		}
		return $input;
	}

	private function loadParams($input, &$modules)
	{
		foreach ($input as $k => $v) {
			foreach ($modules as &$m) {
				$m['params'][$k] = $v;
			}
		}
	}

	public function run($path = null)
	{
		if (!$path) {
			$path = $this->path;
		}
		$res = false;
		$input = $this->getInput();
		if ($auth = $this->map2modules($path, $this->route['auth'])) {
			$this->loadParams($input, $auth);
			Mod::initSequence($auth);
			if ( ! $res = Mod::callSequence(null)) {
				$this->errmsg = "ERROR: authentication failed! ";
				return false;
			}
		}
		if (
			($mods = $this->map2modules($path, $this->route['mod']))  &&
			($views = $this->map2modules($path, $this->route['view']))
		) {
			$this->loadParams($input, $mods);
			Mod::initSequence($mods);
			$res = Mod::callSequence(null);
			$views[0]['params']['data'] = $res;
			Mod::initSequence($views);
			return Mod::callSequence(null);
		} else {
			$this->errmsg = "ERROR: module or viewer failed! [path:" . $this->path . "]";
			return false;
		}
	}

	private function fmtModules($modules)
	{
		if (!is_array($modules)) {
			if (is_string($modules) || is_callable($modules)) {
				$modules = array($modules);
			} else {
				return false;
			}
		}

		foreach ($modules as &$m) {
			if (is_array($m)) {
				continue;
			}
			$mod = array(
				'mod' => null,
				'params' => array(),
			);
			if (is_callable($m) || is_string($m)) {
				$mod['mod'] = $m;
				$m = $mod;
			} else {
				return false;
			}
		}
		return $modules;

	}

	/*
	 *  @param	$modules	array,
	 *  		[ function($a){}, '/admin/user/checkout' ]
	 *  		[ '/admin/modules/{1}/run' ]
	 *  		[ ['mod' => '/admin/modules/{1}/run', 'params' => ['$1'], 'options'=>['xx'] ]
	 */
	public function authenticate($path, $modules)
	{
		if (!$modules = $this->fmtModules($modules)) {
			throw new \Exception("wrong module found, authenticate()");
		}
		$preg = $this->path2preg($path, $route_lev, $star_num);
		$this->route['auth'][$route_lev][$preg] = $this->initModules($modules);
		return $this;
	}

	/*
	 *  @param	$modules	array,
	 *  		[ function($a){}, '/admin/user/checkout' ]
	 *  		[ '/admin/modules/{1}/run' ]
	 *  		[ ['mod' => '/admin/modules/{1}/run', 'params' => ['$1'], 'options'=>['xx'] ]
	 */
	public function location($path, $modules)
	{
		if (!$modules = $this->fmtModules($modules)) {
			throw new \Exception("wrong module found, location()");
		}

		$preg = $this->path2preg($path, $route_lev, $star_num);
		$this->route['mod'][$route_lev][$preg] = $this->initModules($modules);
		return $this;
	}

	/*
	 *  @param	$modules	array,
	 *  		[ function($a){}, '/admin/user/checkout' ]
	 *  		[ '/admin/modules/{1}/run' ]
	 *  		[ ['mod' => '/admin/modules/{1}/run', 'params' => ['$1'], 'options'=>['xx'] ]
	 */
	public function view($path, $modules)
	{
		/* php view file name */
		if (is_callable($modules)) {
			$modules = array(array(
				'mod' => $modules,
				'params' => array(
					'data' => array()
				),
			));
		} elseif (is_string($modules)) {
			$modules = array(array(
				'mod' => '/app/html',
				'params' => array(
					'data' => array(),
					'path' => $modules,
					'fetchPage' => true
				),
				'param_preg' => true
			));
		} else	{
			throw new \Exception("view()");
		}
		$preg = $this->path2preg($path, $route_lev, $star_num);
		$this->route['view'][$route_lev][$preg] = $this->initModules($modules);
		return $this;
	}

	private function map2modules($path, array $route)
	{
		if (isset($route['eq'][$path])) {
			$modules = $route['eq'][$path];
		} elseif ( $modules = $this->match($path, $route['prefix'], $preg)) {
			;
		} elseif ( $modules = $this->match($path, $route['sufix'], $preg)) {
			;
		} else {
			return false;
		}

		$match = false;
		foreach ($modules as &$m) {
			if ($m['mod_preg'] && is_array($m['mod_preg'])) {
				if (!$match && $preg && !preg_match($preg, $path, $match)) {
					throw new \Exception("wrong preg_match pattern of location:". $preg);
				}

				foreach ($m['mod_preg'] as $i => $rp) {
					if ($rp['upper']) {
						$match[$i] = ucfirst($match[$i]);
					}
					$m['mod'] = str_replace($rp['replace'], $match[$i], $m['mod']);
				}
			}

			if ($m['param_preg'] && is_array($m['param_preg'])) {
				if (!$match && $preg && !preg_match($preg, $path, $match)) {
					throw new \Exception("wrong preg_match pattern of location:". $preg);
				}

				foreach ($m['param_preg'] as $pos => $param) {
					foreach ($param as $i => $rp) {
						if ($rp['upper']) {
							$match[$i] = ucfirst($match[$i]);
						}
						$m['params'][$pos] = str_replace($rp['replace'], $match[$i], $m['params'][$pos]);

					}
				}

			}

			$i = 0;
			foreach ($m['params'] as $p) {
				if ($p == '$1') {
					$m['options']['param_from_std'] = $i;
				}
				$i ++;
			}

		}
		return $modules;
	}

	private function match($path, array $route, &$preg) {
		foreach ($route as $preg => $modules) {
			if (preg_match($preg, $path)) {
				return $modules;
			}
		}
		return false;
	}


	private function checkMod(array $mod)
	{
		if (!isset($mod['mod']) || !isset($mod['params'])) {
			throw new \Exception("wrong module params");
		}
		if (!isset($mod['mod_preg'])) {
			$mod['mod_preg'] = false;
		}
		if (!isset($mod['options'])) {
			$mod['options'] = array();
		}
		$mod['options']['param_map'] = true;
		if (!isset($mod['param_preg'])) {
			$mod['param_preg'] = false;
		}
		if (!($callable = is_callable($mod['mod'])) && !is_string($mod['mod'])) {
			throw new \Exception("wrong mod");
		}
		$mod['callable'] = $callable;
		return $mod;
	}
	/*
	 *  @param	$modules	array,
	 *  		[ {'mod' => '/admin/modules/{1}/run',
	 *  		   'params' => {'$1'},
	 *  		   *  'options'=>{'param_map':true},
	 *  		   *  'mod_preg' => false,
	 *  		   *  'param_preg' => true
	 *  		   } ]
	 */
	private function initModules(array $modules)
	{
		$mods = array();
		foreach ($modules as $m_path) {
			$transfer = $this->checkMod($m_path);
			if ($transfer['callable']) {
				$transfer['mod_preg'] = false;
			} else {
				$transfer['mod_preg'] = true;
			}

			if ($transfer['mod_preg']) {
				$transfer['mod_preg'] = $this->pregPos($transfer['mod']);
			}
			if ($transfer['param_preg']) {
				$param_preg = array();
				foreach ($transfer['params'] as $i => $p) {
					if (is_string($p)) {
						$param_preg[$i] = $this->pregPos($p); 
					}
				}
				$transfer['param_preg'] = $param_preg;
			}
			$mods[] = $transfer;
		}
		return $mods;
	}

	private function pregPos($pattern) {
		$preg_pos = array();
		if (preg_match_all('/(\{\d+\^{0,1}\})/', $pattern, $match)) {
			foreach ($match[0] as $chip) {
				if (strpos($chip, "^") !== false) {
					$upper = true;
				} else {
					$upper = false;
				}
				$preg_pos[intval(trim($chip, '{}^'))] = array('upper' => $upper, 'replace' => $chip);
			}
		}
		return $preg_pos;
	}


	public function route($preg, array $modules, $route_lev, $route_type)
	{

	}

//	
//	 1. equal
//	
//	 2. ()  and  *
//	 	2.1	begin with "/" 	>  not begin with "/"
//	 	2.2	equal > () > *  , from left to right
//	
//	 3. preg match
//	
//	
//	 example:
//	
//	 	/(admin|manager)/display >  /*/display  >  admin/display
//	
//	 	admin/display  >  admin/(display|show)  >  admin/*
//	 	>  (admin|manager)/display  >   */display
//	 	>  /^admin\/display$/
//	
//	
	private function path2preg($path, &$route_lev, &$star_num)
	{
		$start_with_le = false;
		$src_path = $path;
		if (substr($path, 0, 1) == "/") {
			if (substr($path, strlen($path) - 1, 1) == "/") {
				$route_lev = self::ROUTE_LEV_SUFIX;
				return $path;
			}
			$start_with_le = true;
		}
		$special_replace = array(
			'/' => "\\/",
			'.' => "\.",
			'-' => "\\-",
			'+' => "\\+",
			'$' => "\\$",
			'^' => "\\^",
		);
		$path = str_replace(array_keys($special_replace), array_values($special_replace), $path);
		$preg = str_replace("*", '([\w%#\$\-_\+=]+)', $path, $star_num);
		if ($start_with_le) {
			if ($star_num == 0) {
				$route_lev = self::ROUTE_LEV_EQU;
				return $src_path;
			} else {
				$route_lev = self::ROUTE_LEV_PREFIX;
				return "/^" . $preg . "/";
			}
		} else {
			$route_lev = self::ROUTE_LEV_SUFIX;
			return "/" . $preg . "/";
		}
	}

	public function getPathSuffix()
	{
		if (preg_match('/^.+(\.[\w0-9-_]+)$/', $this->path, $match)) {
			return $match[1];
		} else {
			return '';
		}
	}

	/*
	 * @param	$rules,     [$path => $modules], see Web::route() function
	 */
	public function rules(array $rules)
	{
		foreach ($rules as $path => $modules) {
			$this->route($path, $modules);
		}
	}

	private $errmsg;
	public function lastError()
	{
		return $this->errmsg;
	}


	private $tplRoot = "";

	private $tplSuffix = ".php";

	public function setTplRoot($tplRoot)
	{
		$this->tplRoot = rtrim($tplRoot, DIRECTORY_SEPARATOR);
	}

	public function setTplSuffix($tplSuffix)
	{
		$this->tplSuffix = $tplSuffix;
	}

	public function html($fetchPage = false)
	{
		$doc = $this->output();
		if ($this->lastError()) {
			return false;
		}
		return html(
			$doc,
			$this->tplRoot . DIRECTORY_SEPARATOR . trim($this->getModulePath(), DIRECTORY_SEPARATOR) . $this->tplSuffix,
			$fetchPage
		);
	}
			

	public function json()
	{
		return json_encode($this->output());
	}
}
