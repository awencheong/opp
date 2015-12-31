<?php
namespace	myapp\modules;
class Services
{
	public function run($name, $title)
	{
		return array("title" => $title, "name" => $name);
	}
}
