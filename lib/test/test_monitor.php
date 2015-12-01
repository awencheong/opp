<?php
/*
	监控微服务

	
	
	时间消耗的监控 (例如脚本)	
			创建数据集:	http://mon.com/cube/create?name=time&dimensions=["time", "url", "value"]
			上传数据:	http://mon.com/cube/collect?data=[["20150711 11:00:03", "/all/books", 1001]]&name=time


	m = new TimeMonitor("/all/books", "db_books");
	m->start();
	...
	m->end();	获取一个操作所消耗的时间

		|
		|
		|
		|
		|
		|
		|----------------------------

	

	内存消耗的监控 (例如脚本)	
			创建数据集: 	http://mon.com/cube/create?name=memory&dimensions=["time", "url", "value"]
			上传数据:	http://mon.com/cube/collect?data=[["20150711 11:00:03", "/all/books", 1001]]&name=memory
	
	m = new MemoryMonitor("/all/books");
	m->start();
	...
	m->end();	获取一个操作所消耗的内存

		|
		|
		|
		|
		|
		|
		|----------------------------


	内存峰值的监控 (例如脚本)	
			创建数据集: 	http://mon.com/cube/create?name=memory_peark&dimensions=["time", "url", "value"]
			上传数据:	http://mon.com/cube/collect?data=[["20150711 11:00:03", "/all/books", 1001]]&name=memory_peak
	
	m = new MemoryPeakMonitor("/all/books");
	m->start();
	...
	m->end();	获取一个操作所消耗的内存

		|
		|
		|
		|
		|
		|
		|----------------------------
	



	业务数据的监控 (多维数据)	
			创建数据集: 	http://mon.com/cube/create?name=pub&dimensions=["year", "month", "publisher", "offer", "value"]
			修改数据集: 	http://mon.com/cube/modify?name=pub&ukey=["year", "month", "publisher", "offer"]			//设置唯一键
			上传数据:	http://mon.com/cube/collect?name=pub&data=[["2011","07","p01","off01",100]]
			监控条件设置（大于）:	http://mon.com/alarm/rule?name=pub&condition=grater_than(100)&response=last_7		//当写入的值大于 100时，报警； 警报被触发时，返回最近7个数据点
			监控条件设置（波动）:	http://mon.com/alarm/rule?name=pub&condition=without(0.3)&response=last_7			//当写入的值跟上一次比较，波动范围达到 30% 时，报警
			监控条件设置（横向比较）:	http://mon.com/alarm/rule?name=pub&condition=custom_1(0.3)&response=last_2_7	//当写入的值跟上一次比较，波动范围达到 30% 时，报警, 返回两个数据源各自的最近7个数据点
			设置预警信息接收人:		http://mon.com/alarm/receiver?name=pub&email=awen@aa.com					//设置预警邮箱
			设置预警信息接收人:		http://mon.com/alarm/receiver?name=pub&phone=13800000000					//设置预警微信
			设置预警信息接收人:		http://mon.com/alarm/receiver?name=pub&user=awen						//设置预警用户(websock)
			发送预警(邮件,微信,websock):	http://mon.com/alarm/send?name=pub&content=[subject,content,desc]


			//  出于安全性考虑，以下 定义监控条件，监控响应 的接口将内置，不对外开放

			定义监控条件（大于）:	http://mon.com/cube/alarm/condition?name=grater_than
						rule='
						function grater_than($val) {
							return $this->vaule > $val;
						}'

			定义监控条件（波动）:	http://mon.com/cube/alarm/condition?name=without
						rule='
						function without($val) {
							$diff = abs($this->vaule - $this->last(1)->value);
							return abs($diff / $this->value) > $val;
						}'

			定义监控条件（跟其他数据源横向比较）:	http://mon.com/cube/alarm/condition?name=cumstom
						rule='
						function custom_1($val) {
							$other_cube = Cube::Set('m_pub');
							$to_cmp = $other_cube->get(array($this->year, $this->month, $this->publisher, $this->offer))->value;
							$diff = abs($this->value - $to_cmp);
							return abs($diff / $this->value) > $val;
						}'


			定义监控响应内容（返回最近7个数据点的数据）:	http://mon.com/cube/alarm/response?name=last_7
						response='
						function last_7($compare, $cube) {
							$list = array();
							foreach ($cube->last(7) as $p) {
								$list[] = $p->to_array();
							}
							return $list;
						}'

			定义监控响应内容（返回两个数据源最近7个数据点的数据）:	http://mon.com/cube/alarm/response?name=last_2_7
						response='
						function last_2_7($compare, $cube, $other_cube) {
							return array($cube->last(7)->to_array(), $other_cube->last(7)->to_array());
						}'




	m = new CubeMonitor("time", "publisher", "offer");
	m->collect("2011", "07", "p01", "off01", 100);	//收集一个数据点

	
		|
		|
		|
		|
		|
		|
		|----------------------------
		|----------------------------
		|----------------------------


	// 监控条件中，自定义规则所用到的 数据集 类
	class	Cube {
		//返回倒数第n个数据
		public function last($n) {
		}

		//返回符合条件的若干个数据集
		public function list($condition) {
		}

		public function __construct($cube_name) {
		}
	}

	

	查询功能：
	http://mon.com/list?index=pub_off[2015][07][110]	//返回 publisher 110 在 2015/07 的所有数据 array()
	http://mon.com/sum?index=pub_off[2015][07][110]		//返回 publisher 110 在 2015/07 的所有数据 总和
	

