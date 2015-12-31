<?php
namespace	app\test\lib;
use	app\Web;
use	app\Tester;
class	TestWeb
{
	public function TestPost()
	{
		$web = new Web();
		$web->setModuleRoot("app\\test");
		$web->route("/user/article/modify", "lib\\WebData\\modify > \\app\\json");

		$web->path = "/user/article/modify.php";
		$web->method = "POST";
		$web->post = array(
			"name"=>"awen",
			"article"=>realpath(__FILE__)
		);
		$data = json_decode($web->output(), true);
		Tester::assert(isset($data['name']) && $data['name'] === 'awen');
		Tester::assert(isset($data['content']));

	}

	public function TestGet()
	{
		$web = new Web();
		$web->route("/user/article/modify", "\\app\\test\\lib\\WebData\\modify > \\app\\json");

		$web->path = "/user/article/modify";
		$web->method = "GET";
		$web->get = array(
			"name"=>"awen",
			"article"=>realpath(__FILE__)
		);
		$data = json_decode($web->output(), true);
		Tester::assert(isset($data['name']) && $data['name'] === 'awen');
		Tester::assert(isset($data['content']));
	}

	public function TestHtml()
	{
		$web = new Web();
		$web->setModuleRoot("app\\test");
		$web->setTplRoot(__DIR__);
		$web->route("/article", "lib\\WebData\\article");

		$web->path = "/article.php";
		$web->method = "GET";
		$title = "this title is for test ...";
		$content = "this content is also, for test ...";
		$web->get = array(
			"title"=> $title,
			"content"=> $content
		);
		$data = $web->html(true);
		$compare = "<html><head>$title</head><body>$content</body></html>\n";
		Tester::assert($data === $compare);
	}

	public function TestHtmlOom()
	{
		$web = new Web();
		$web->setModuleRoot("app\\test");
		$web->setTplRoot(__DIR__);
		$web->route("/article", "lib\\WebData\\article");

		$web->path = "/article.php";
		$web->method = "GET";
		$chip = "this title is for test ...";
		$chip2 = "this content is also, for test ...";
		$title = $content = "";
		for ($i = 0; $i < 600000; $i ++) {
			$title .= $chip1;
			$content .= $chip2;
		}
		$beginMem = memory_get_usage();
		$web->get = array(
			"title"=> $title,
			"content"=> $content
		);
		for ($i = 0; $i < 100; $i ++) {
			$data = $web->html(true);
		}
		$endMem = memory_get_usage();
		Tester::assert($endMem < $beginMem * 2);
	}
	
}	

class WebData
{
	public function modify($name, $article)
	{
		if (!$fp = fopen($article, "r")) {
			return false;
		}
		$line = fgets($fp);
		fclose($fp);
		return array("name" => $name , "content"=>" hello, content we got here is:$line", "article"=>$article);
	}

	public function article($title, $content)
	{
		return array("title"=>$title, "content"=>$content);
	}
}

