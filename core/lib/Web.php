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

	private $mod = null;

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
		$this->mod = new Mod;
	}

	public function getModulePath()
	{
		if (preg_match('/(^.+)\.\w+$/', $this->path, $match)) {
			return $match[1];
		} else {
			return $this->path;
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

	public function setModuleRoot($namespace)
	{
		$this->mod->setModuleRoot($namespace);
	}

	private $rules = array();	// 'path' => [['class', 'method']]
	public function route($path, $modules) 
	{
		if (!is_string($modules) && !is_array($modules)) {
			return false;
		}
		if (is_string($modules)) {
			$modules = array($modules);
		}
		if (empty($modules)) {
			return false;
		}
		$this->rules[$path] = $modules;
		return $this;
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

	public function output()
	{
		$modPath = $this->getModulePath();
		$res = false;
		if ($this->method == "GET") {
			$input = $this->get;
		} else if ($this->method == "POST") {
			$input = $this->post;
		} else {
			$input = array();
		}
		if (isset($this->rules[$modPath])) {
			foreach ($this->rules[$modPath] as $r) {
				try {
					$res = $this->mod->call($r, $input);
				} catch (\Exception $e) {
					$this->errmsg = "ERROR:" . $e->getMessage();
					return false;
				}
			}
			return $res;
		} else {
			$this->errmsg = "ERROR: path not exists {$modPath}";
			return false;
		}
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
