<?php
namespace	app;
function json($input)
{
	return json_encode($input);
}

function html($data, $path, $fetchPage = false)
{
	if (!is_array($data)) {
		$data = array("doc" => $data);
	}
	extract($data, EXTR_PREFIX_SAME, "_");
	if ($fetchPage) {
		ob_start();
	}
	require($path);
	if ($fetchPage) {
		$page = ob_get_contents();
		ob_end_clean();
		return $page;
	}
}
