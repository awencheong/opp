<?php
namespace module\user;

class dosth {
	public function run($params) {
		opp()->render->set_path("user/login.html");
		return opp()->module->call('/user/login', $params);
	}
}
