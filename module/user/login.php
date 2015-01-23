<?php
namespace module\user;
class login {
	public function run($params) {
		if (isset($params['uid']) && is_numeric($params['uid']) && ($uid = intval($params['uid']))) {
			;
		} else {
			return false;
		}
		$data = opp()->my_db->query('select * from role_attr where roleid='.$uid);
		$info = array();
		foreach ($data as $d) {
			switch ($d['attr_type']) {
			case 1:
				$info[$d['attr_name']] = $d['attr_value'];
				break;

			case 2:
				$info[$d['attr_name']] = $d['tag_value'];
				break;

			case 3:
				$info[$d['attr_name']] = $d['tag_desc'];
				break;

			} 
		}
		return array("game" => VSTAR_GAME_NAME, "id"=>"100", "job"=>"teacher", "data"=>$info);
	}
}
