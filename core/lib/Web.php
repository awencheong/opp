<?php
/*
	app()->web->route("/user/login/json", ["\\myapp\\modules\\user\\login | \\app\\json"]);
	app()->web->setModuleRoot("\\myapp\\modules");
	app()->web->route("/user/info/modify", ["\\myapp\\modules\\user\\login", "\\myapp\\modules\\user\\info\\modify > \\app\\json"]);	//按顺序执行每个module, 返回最后一个module的值
	app()->web->route("/user/info/modify", ["user\\login", "user\\info\\modify > \\app\\json"]);	//按顺序执行每个module, 返回最后一个module的值

	app()->web->path = "/article/get.php";
	app()->web->method = "GET";
	app()->web->get = array("type"=>"note","limit"=>10);
	echo app()->web->output();

	app()->web->path = "/article/put.php";
	app()->web->method = "POST";
	app()->web->post = array("type"=>"note","contents"=>"@/tmp/articles");
	echo app()->web->html();
	echo app()->web->json();
	print_r(app()->web->run());
*/

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

	public function run($path = null)
	{
		if (!$path) {
			$path = $this->path;
		}
		$res = false;
		$input = $this->getInput();
		if (
			($mods = $this->map2modules($path, $this->route['mod']))  &&
			($views = $this->map2modules($path, $this->route['view']))
		) {
			foreach ($input as $k => $v) {
				foreach ($mods as &$m) {
					$m['params'][$k] = $v;
				}
			}
			Mod::initSequence($mods);
			$res = Mod::callSequence(null);
			$views[0]['params']['data'] = $res;
			Mod::initSequence($views);
			return Mod::callSequence(null);
		} else {
			$this->errmsg = "ERROR: path not exists {$modPath}, no rule found";
			return false;
		}
	}

	/*
	 *  @param	$modules	array,
	 *  		[ function($a){}, '/admin/user/checkout' ]
	 *  		[ '/admin/modules/{1}/run' ]
	 *  		[ ['mod' => '/admin/modules/{1}/run', 'params' => ['$1'], 'options'=>['xx'] ]
	 */
	public function location($path, $modules)
	{
		if (!is_array($modules)) {
			if (is_string($modules)) {
				$modules = array($modules);
			} else {
				throw new \Exception("location()");
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
				throw new \Exception("wrong module found, location()");
			}
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
				if (!$match && !preg_match($preg, $path, $match)) {
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
				if (!$match && !preg_match($preg, $path, $match)) {
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

	private function path2preg($path, &$route_lev, &$star_num)
	{
		$start_with_le = false;
		$src_path = $path;
		if (substr($path, 0, 1) == "/") {
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
