<?php
	namespace	opp\request;

	/* Web 请求Router 类， 提供WEB请求的所有参数 */
	class web implements \IRequest {
		private $params = array();
		private $path = false;
		private $format = false;

		public function __construct() {
			if (isset($_SERVER['REQUEST_URI'])) {
				$uri = $_SERVER['REQUEST_URI'];
				if (preg_match('/^(.*)\.(\w+)(\?.*)?$/', $uri, $match)) {
					$this->path = $match[1];
					$this->format = $match[2];
				}
			}
			if (!empty($_GET)) {
				foreach ($_GET as $key => $val) {
					$this->params[$key] = $val;
				}
			}
			if (!empty($_POST)) {
				foreach ($_POST as $key => $val) {
					$this->params[$key] = $val;
				}
			}
		}

		public function get_format() {
			return $this->format;
		}

		public function get_host() {
			return $_SERVER['HTTP_HOST'];
		}

		public function get_header($header_name = NULL) {
			return "mobile";
		}

		public function get_path() {
			return $this->path;
		}	

		public function get_params() {
			return $this->params;
		}

		public function get_cookie($cookie_name=null) {
			if (!$cookie_name) {
				return $_COOKIE;
			} else if (isset($_COOKIE[$cookie_name])) {
				return $_COOKIE[$cookie_name];
			} else {
				return false;
			}
		}
	}


