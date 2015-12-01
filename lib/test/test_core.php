<?php

	/*  监控API / GUI (成型为微服务?)
		任何一个url请求，都能通过监控API查看到其 内存使用量, 执行时间, 各个IO所需时间;  
		当设定的规则被触发时，警告会发往指定的邮箱/手机, 同时检查所涉及的代码模块，抽取出最近的提交更改，作为排查的参考信息
		

	    文档API / GUI (成型为微服务?)
		任何一个php/js/其他文件，都能通过文档API录入, 查看，搜索;  
		当项目被纳入监控服务的范围内时，任何一个开发成员对代码做的任何改动，都会自动推送到其他人的邮箱里，每个人都能够check到他所写的代码~~

	    
		
	    成型模块的复用问题，交给专业的 docker ~~



	/*prepare*/
	//require(__DIR__ . "/../../autotest.php");		-->   等于 require(__DIR__ . "/../../global.php");	app()->load(APP_ROOT . '/test.conf.php');
	require(__DIR__ . "/../../global.php");	
	app()->load_test(APP_ROOT . '/test.conf.php');	// io 为 Recoder(io) 

	//=============  0. 查看文档
	app()->document("/app/document");	//查看文档（当参数所指的是个文件时<可以省略后缀 .doc>, 直接返回改文件的内容；  当参数所指是个目录时，遍历该文件下所有后缀为 .doc 的文件，返回其内容）

	//=============  1. 初始化一个 io 
	app()->db = new \app\io\db\mysqli("localhost", 3306, "dbname", "user", "passwd");
	app()->lock("db");	/* 锁定该io, 使得其不会被覆盖； 部分系统自带的 io 无法通过 setter() 进行覆盖 */
	

	//=============  2. 查看一个io的文档
	print_r(app()->document('\app\io\db\mysqli'));
	echo (app()->document('\app\io\db\mysqli', DOC_CONSOLE_COLORFULL);	//控制台，彩屏
	echo (app()->document('\app\io\db\mysqli', DOC_HTML_COLORFULL);		//html 页面，彩屏

	//=============  3. 使用一个 io 的 method
	// 清空表
	app()->db->clean('user');
	// 插入若干记录
	app()->db->query('insert into user value ("awen","20010403", "1234")');
	// 批量插入记录
	app()->db->insert('user', $list);
	// 批量更新记录
	app()->db->update('user', $list, 'name.stdno' /* unique key */);
	// 批量更新记录
	app()->db->replace('user', $list, 'name.stdno' /* unique key */);

	//=============	 * 查询 recorder 的文档
	print_r(app()->document('app/tools/io/recorder'));

	//=============	 4. 设置一个io的记录器 (永久存储)
	app()->db->set_recorder("/data/app/test_cases/io/db/");

	//=============  5. 指定一个io的返回值
	app()->db->set_return('query'/* method */, array('select * from `user` where id = 121') /* params */, array('name'=>'awen', 'age'=>15) /* value to return*/);	//在io记录器中设置返回值（如果设置了 recorder 文件, 见set_recorder()，记录到文件中中）

	//=============  6. “指定”生效
	app()->db->query('select * from `user` where id = 121');	//当在 io记录器 中发现参数值匹配的时候，会返回该记录值；否则从 io记录器 中查找

	//=============  7. 关闭 recorder 的 readout 功能 (即直接从 io 中读取数据)
	app()->db->no_reading();

	//=============  8. 关闭 recorder 的 writein 功能 (保护 recorder 的数据不被意外变更 )
	app()->db->no_writing();

	//=============  9*. 全程记录io (跟 set_recorder() 不同， 它 ”1.只从io中读取数据; 2.同时将 return value 记录到文件中")
	app()->db->record("/data/app/test_cases/io/db");		//记录所有 method 的 io
	app()->db->record("/data/app/test_cases/io/db", "query");	//记录 query 的 io


	//=============  10. 获取所有关键字
	app()->document('keywords'); 


	//=============  * 查询 request 的文档
	app()->document("/app/io/request");

	app()->request->post("name");	//should return null
	app()->request->get("name");	//should return awen
	app()->request->param("name");	//should return awen
	app()->request->path();
	app()->request->params();
	app()->request->method();	//should return true
	app()->request->set_method('post');	//should return true
	app()->request->set_cookie('abc', "123");
	app()->request->set_header('abc', "123");
	app()->request->set_session('abc', "123");

	//=============  11. 发送网络请求
	// 文档
	app()->document("/app/io/curl");
	// 发送post网络请求
	app()->post($path, $params);
	app()->curl->post($path, $params);	//系统默认自带，使用者可以覆盖它
	// 发送get网络请求
	app()->get($path, $params);		
	app()->curl->get($path, $params);	//系统默认自带，使用者可以覆盖它
	// 发送put网络请求
	app()->put($path, $params);		
	app()->curl->put($path, $params);	//系统默认自带，使用者可以覆盖它
	// 发送delete网络请求
	app()->delete($path, $params);
	app()->curl->delete($path, $params);	//系统默认自带，使用者可以覆盖它

	//=============  12. 调用模块
	// 文档
	app()->document("/app/io/module");
	app()->call($path, $params);
	app()->module->call($path, $params);	//系统默认自带，且处于保护状态，使用者无法覆盖
	


	//=============  13. 监控io
	app()->monitor = new \app\io\monitor();		//系统默认自带， 且处于保护状态，使用者无法覆盖
	app()->monitor->watch(array(app()->db, "query"), "/data/app/monitor/db/query.monitor", "1min");	//监控 db->query() 函数，每分钟 记录一个信息
	app()->monitor->watch(array(app()->db, "query"), "/data/app/monitor/db/query.monitor", "1/15");	//监控 db->query() 函数, 每15次 记录一个信息点
	app()->watch("db.query", "/data/app/monitor/db.monitor", "1min");	

	app()->monitor->info("/data/app/monitor/db/query.monitor");	//返回 一系列时间点上的 （内存使用，cpu使用， 开始结束时间)
	app()->monitor->list("/data/app/monitor/db/");		//递归罗列 目录下所有的 监控数据文件



	//=============  14. io 缓存 
	app()->db_cache = new \app\cache\db\memcached();	//用户自定义
	app()->cache(array(app()->db, "query"), array(app()->db_cache, "query"));	//当调用 app()->db->query() 的时候，会先调用 app()->db_cache->query(), 如果返回值 === false; 再调用 app()->db_cache->query()
	//app()->cache(array(app()->db, "query", "select * from user_info"), array(app()->db_cache, "query"));	//缓存级别到了 "某个特定参数的时候才读缓存"	************  这个已经不适合在框架层面上做了， 它应该具体到了某个功能， 应该在应用层面去实现~
	/* 简版 */
	app()->cache("db.query","db_cache.query");	//当调用 app()->db->query() 的时候，会先调用 app()->db_cache->query(), 如果返回值 === false; 再调用 app()->db_cache->query()


	//=============  15. io 备份 
	app()->db_backup = new \app\backup\monitor\memcached();
	app()->backup(array(app()->db, 'update'), array(app()->db_backup, 'update'));	// 当调用 app()->db->update() 成功之后，会接着调用 app()->db_backup->update();  db_backup->update() 的调用成功与否不影响 db->update() 的返回结果
	/* 简版 */
	app()->backup("db.update", "db_backup.update");	// 当调用 app()->db->update() 成功之后，会接着调用 app()->db_backup->update(), 把( 参数 和 返回结果 ) 都传过去;  db_backup->update() 的调用成功与否不影响 db->update() 的返回结果


	//=============  16. io 重定向
	app()->http->header('content-type', 'application/csv');	//添加http头信息(以供下载)
	app()->download_csv = app()->pipe(
		array(app()->db, 'query'),	//取出数据
		array(app()->csv, 'encode'),	//转换成csv文件
		array(app()->http, 'put_content')	
	);
	app()->download_csv->query("select * from user_info");	//执行 pipe 第一个io method


	//=============  *. 使用示例： 使用redis 缓存常用数据
	app()->mem_0 = new \app\io\memcached("localhost", 11211);
	$userinfo = new \app\modules\user_info();
	app()->user_cache = new \app\cache\cache($userinfo, app()->mem_0);	//传 app()->db 进去，当 cache 命中失败的时候，就有机会从 app()->db 里读取相应的数据回来了
	app()->cache(array($userinfo, "getInfo"), array(app()->user_cache, "getInfo"));	

	//.... 还是简单点好    （剩下的就自行在目录 app/cache/modules/userinfo 下面封装吧）
	app()->userinfo = new \app\modules\user_info();
	app()->userinfo_cache = new \app\cache\user_info\memcached(app()->userinfo);
	app()->cache("userinfo.getInfo", "userinfo_cache.getInfo"); 

	//.... 进一步地发现， 还不如就直接在 class \modules\userinfo {}  里面实现算了
	
	//.... 所以 ，这块是个鸡肋？？？

	class hash_cache_and_backup {
		private function __construct($io) {
			$this->cache = new Memcached();
		}

		public function __call($name, $params) {
			if ($res = $this->cache->get(hash($name, $params)) {
				return $res;
			} else {
				if ($res = $this->io->$name($params)) {
					$this->cache->set(hash($name, $params), array($params, $res));	
				}
				return $res;
			}
		}
	}


	//===============  *. 可以扩展为其他形式，以兼容项目老代码（如果有的话）
	app()->backup("monitor.write", array(app()->monitor_cache, "write"));	//缓存对象是个 obj->method	/*重点在于： 参数形式必须和 __monitor.read() 的参数保持一致
	app()->backup("monitor.write", array("\app\cache\monitor\memcached", "write"));	//缓存对象是个 Class::Method
	app()->backup("monitor.write", "write");	//缓存对象是个 function


	

	//===============  16. 文档类
	app()->add_document_root("/path/of/document/root");
	app()->add_document_root("/path/of/document/root2");
	app()->write_document("/module/user/login", $content);	//把 content 写入 document
	app()->append_document("/module/user/login", $content);	//把  content Append 到 document 后面
	app()->document("/module/user/login");			//返回所需要的 document	string
	app()->list_documents();				//该函数搜索所有 add_document_root() 加入的目录及其子目录，获取后缀名为 .doc 的文件	['/path/of/document1', '/path/of/document2', '/path/of/document3']

	class Document {
		public function __construct($file) {
		}
		public function write(array $content) {
		}
		public function read() {	//return array $content
		}
		public function append(array $content) {
		}
	}

	class DocumentDir {
		public function __construct($dir) {
		}
		public function lists() {	//找出所有 已实现&已撰写文档  的 file
		}
		public function to_document() {
		}	//找出还未写文档的那些 php file
		public function to_implement() {
		}	//找出写了文档，但还未实现的那些 doc
	}




