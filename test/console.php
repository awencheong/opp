<?php
require __DIR__ . "/../core/console.php";
Console::init(array(
	
        "campaign" => 'required',    //    (必要参数)

        "--all"=> 1,        // --option (可选参数, 不带子参数)

        "--campaign"=>array(  // --option (可选参数, 带子参数)
            0 => "int",
        ),
));

print_r(Console::$params);
