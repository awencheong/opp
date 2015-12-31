<?php
namespace	myapp\modules;
class Index
{
	public function run($name, $title)
	{
		return array("title" => $title, "name" => $name);
	}
}
