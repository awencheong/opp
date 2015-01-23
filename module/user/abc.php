<?php
namespace module\user;
class abc {
	public function run($params) {
		$data = opp()->module->call("/user/login", ['uid' => 15]);
		return array("game" => VSTAR_GAME_NAME, "id"=>"100", "job"=>"teacher", "data"=>$info, "another"=>$data);
	}
}
