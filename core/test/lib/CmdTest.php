<?php

class CmdDebugMod
{

    public function analyze($name, $age, array $detail)
    {
        return array(
            array(
                "name" => $name,
                "age" => $age
            ),
            $detail
        );
    }

    public function table(array $head, array $rows, $null = '-')
    {
        foreach ($rows as &$r) {
            $tmp = array();
            foreach ($head as $h) {
                if (isset($r[$h])) {
                    $tmp[] = $r[$h];
                } else {
                    $tmp[] = $null;
                }
            }
            $r = $tmp;
        }
        return array(
            'head' => $head,
            'body' => $rows
        );
    }
}

class CmdTest extends PHPUnit_Framework_TestCase
{

    public function testCmd()
    {
        return true;
        
        $fpath = __DIR__ . "/abc.json";
        $res = file_put_contents($fpath, '{"name":"awen1"}');
        $this->assertEquals(true, $res !== false);
        $arg = array(
            "./exec.php",
            "CmdDebugMod/analyze",
            "awen",
            "12",
            "@./abc.json",
            "--CmdDebugMod/table",
            '["name", "age"]',
            '$1'
        );
        $c = new Cmd1($this->prepare());
        $this->assertEquals($c->script, "./exec.php");
        $this->assertEquals($c->param[1], "CmdDebugMod/analyze");
        $this->assertEquals($c->param[2], "awen");
        $this->assertEquals($c->param[3], array(
            "name" => "awen1",
            "age" => 13
        ));
        $this->assertEquals($c->options['CmdDebugMod/table'], 
            array(
                '$1',
                array(
                    "name",
                    "age"
                )
            ));
        unlink($fpath);
    }

    public function testMod()
    {
        $c = new Cmd1();
        $c->cmds = array(
            'CmdDebugMod/analyze' => array(
                'awen',
                '12',
                array(
                    "name" => "awen1"
                )
            ),
            'CmdDebugMod/table' => array(
                array(
                    'name',
                    'age'
                ),
                '$1'
            )
        );
        $res = $c->exec("/");
        
        $this->assertEquals($res['head'], array(
            "name",
            "age"
        ));
        $this->assertEquals($res['body'][0], array(
            "awen",
            "12"
        ));
        $this->assertEquals($res['body'][1], array(
            "awen1",
            "-"
        ));
    }
}
