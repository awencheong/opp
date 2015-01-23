<?php
namespace module\user;

class dosth {
	public function run($params) {
		app()->render->set_path("user/login.html");
		return app()->module->call('/user/login', $params);
	}
}
