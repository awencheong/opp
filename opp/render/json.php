<?php
	namespace opp\render;

	class json implements \IRender {
		public function render($path, $params = array()) {
			return $this->get_result($params);
		}

		public function set_path($path) {
		}

		public function get_result($params = array()) {
			return json_encode($params);
		}
	}
