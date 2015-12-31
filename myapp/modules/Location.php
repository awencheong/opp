<?php
namespace	myapp\modules;
class Location
{
	public function run($name, $title)
	{
		return array("title" => $title, "name" => $name);
	}
}
