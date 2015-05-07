<?php
require	"./opp/app.php";
app()->load("test",new test, App::TYPE_DB);
app()->test->show();
